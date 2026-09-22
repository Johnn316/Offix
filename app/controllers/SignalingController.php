<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Document;

class SignalingController extends Controller
{
    // POST /api/signal/send
    public function send(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body || empty($body['room_id']) || empty($body['type'])) {
            $this->json(['ok' => false, 'error' => 'Missing fields']);
            return;
        }

        $allowedTypes = ['join', 'leave', 'offer', 'answer', 'candidate'];
        if (!in_array($body['type'], $allowedTypes, true)) {
            $this->json(['ok' => false, 'error' => 'Invalid type']);
            return;
        }

        $model      = new Document();
        $receiverId = isset($body['receiver_id']) && $body['receiver_id'] !== null
            ? (int) $body['receiver_id']
            : null;

        $model->sendSignal(
            (string) $body['room_id'],
            (int)    $_SESSION['user_id'],
            $receiverId,
            (string) $body['type'],
            (array)  ($body['payload'] ?? [])
        );

        $this->json(['ok' => true]);
    }

    // GET /api/signal/poll?room={id}&after={lastId}
    public function poll(): void
    {
        $roomId  = (string) ($_GET['room']  ?? '');
        $afterId = (int)    ($_GET['after'] ?? 0);

        if (!$roomId) { $this->json([]); return; }

        $model = new Document();

        // Probabilistic cleanup (1 in 30 requests)
        if (rand(1, 30) === 1) $model->cleanOldSignals();

        $this->json($model->pollSignals($roomId, (int) $_SESSION['user_id'], $afterId));
    }

    // POST /api/snapshot
    public function snapshot(): void
    {
        try {
            ini_set('display_errors', '0');
            ini_set('html_errors', '0');

            $body = json_decode(file_get_contents('php://input'), true);
            if (!$body || empty($body['doc_id']) || !isset($body['content'])) {
                $this->json(['ok' => false, 'error' => 'Missing fields']);
                return;
            }

            $uid = $_SESSION['user_id'] ?? null;
            if (!$uid) {
                $this->json(['ok' => false, 'error' => 'No session user_id']);
                return;
            }

            $model = new Document();
            $doc   = $model->find((string) $body['doc_id']);
            if (!$doc) { $this->json(['ok' => false, 'error' => 'Doc not found']); return; }

            $content  = (string) $body['content'];
            $editorId = (int) $uid;
            $model->saveContent($doc['id'], $content, $editorId);

            try {
                $model->saveVersion($doc['id'], $content, $editorId);
            } catch (\Throwable $e) {
                error_log('Version save failed: ' . $e->getMessage());
            }

            $this->json(['ok' => true, 'ts' => time()]);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    // GET /api/doc/state?id={docId}&ts={unixTs}
    public function docState(): void
    {
        try {
            ini_set('display_errors', '0');
            ini_set('html_errors', '0');

            $docId = (string) ($_GET['id'] ?? '');
            $ts    = (int)    ($_GET['ts'] ?? 0);

            if (!$docId) { $this->json(['changed' => false]); return; }

            $model   = new Document();
            $state   = $model->getState($docId);
            $editors = $model->activeEditors($docId);

            if (!$state) { $this->json(['changed' => false]); return; }

            $docTs   = (int) $state['ts'];
            $changed = $docTs > $ts && $state['content'] !== null;

            $this->json([
                'changed' => $changed,
                'content' => $changed ? $state['content'] : null,
                'ts'      => $docTs,
                'editors' => $editors,
            ]);
        } catch (\Throwable $e) {
            error_log('docState error: ' . $e->getMessage());
            $this->json(['changed' => false, 'error' => $e->getMessage()]);
        }
    }

    // POST /api/doc/heartbeat
    public function heartbeat(): void
    {
        try {
            ini_set('display_errors', '0');
            ini_set('html_errors', '0');

            $body = json_decode(file_get_contents('php://input'), true);
            if (!$body || empty($body['doc_id'])) {
                $this->json(['ok' => false, 'error' => 'Missing doc_id']);
                return;
            }

            $uid = (int) ($_SESSION['user_id'] ?? 0);
            if (!$uid) {
                $this->json(['ok' => false, 'error' => 'Not authenticated']);
                return;
            }

            $model = new Document();
            $model->sendSignal(
                (string) $body['doc_id'],
                $uid,
                null,
                'heartbeat',
                ['userName' => $_SESSION['user_name'] ?? 'User']
            );

            $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            error_log('heartbeat error: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
