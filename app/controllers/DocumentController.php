<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Document;

class DocumentController extends Controller
{
    private Document $model;

    public function __construct()
    {
        $this->model = new Document();
    }

    public function index(): void
    {
        $this->render('documents.index', [
            'pageTitle' => __('docs.title'),
            'documents' => $this->model->all(),
        ]);
    }

    public function create(): void
    {
        $id = $this->model->create((int) $_SESSION['user_id']);
        $this->redirect('/documents/' . $id);
    }

    public function show(string $id): void
    {
        $doc = $this->model->find($id);
        if (!$doc) $this->abort(404, 'Document not found');

        $this->render('documents.show', [
            'pageTitle' => htmlspecialchars($doc['title']),
            'doc'       => $doc,
        ]);
    }

    // Renders the iframe content — standalone editor page
    public function frame(string $id): void
    {
        $doc = $this->model->find($id);
        if (!$doc) $this->abort(404, 'Document not found');

        $this->render('documents.frame', [
            'pageTitle' => $doc['title'],
            'doc'       => $doc,
            'userId'    => (int) $_SESSION['user_id'],
            'userName'  => $_SESSION['user_name'] ?? 'Unknown',
        ], 'frame_layout');
    }

    // AJAX — update title, returns JSON
    public function updateTitle(string $id): void
    {
        header('Content-Type: application/json');
        $doc = $this->model->find($id);
        if (!$doc) { echo json_encode(['ok' => false]); return; }

        $body  = json_decode(file_get_contents('php://input'), true);
        $title = trim($body['title'] ?? 'Untitled Document');
        if (!$title) $title = 'Untitled Document';

        $this->model->updateTitle($id, $title);
        echo json_encode(['ok' => true, 'title' => $title]);
    }

    public function delete(string $id): void
    {
        $doc = $this->model->find($id);
        if (!$doc) $this->abort(404, 'Document not found');

        $isOwner = (int) $doc['owner_id'] === (int) $_SESSION['user_id'];
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        if (!$isOwner && !$isAdmin) {
            $_SESSION['flash_error'] = __('docs.no_permission');
            $this->redirect('/documents');
            return;
        }

        $this->model->delete($id);
        $_SESSION['flash_success'] = __('docs.deleted');
        $this->redirect('/documents');
    }

    public function versions(string $id): void
    {
        $doc = $this->model->find($id);
        if (!$doc) $this->abort(404, 'Document not found');

        $this->render('documents.versions', [
            'pageTitle' => __('docs.versions') . ' — ' . htmlspecialchars($doc['title']),
            'doc'       => $doc,
            'versions'  => $this->model->versions($id),
        ]);
    }

    public function restore(string $id): void
    {
        $body      = $_POST;
        $versionId = (int) ($body['version_id'] ?? 0);

        $version = $this->model->findVersion($versionId);
        if (!$version || $version['document_id'] !== $id) {
            $_SESSION['flash_error'] = 'Version not found.';
            $this->redirect('/documents/' . $id . '/versions');
            return;
        }

        $this->model->saveContent($id, $version['content']);
        $this->model->saveVersion($id, $version['content'], (int) $_SESSION['user_id']);

        $_SESSION['flash_success'] = __('docs.restored');
        $this->redirect('/documents/' . $id);
    }
}
