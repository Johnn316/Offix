<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomeController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        $counts = [
            'contacts'      => (int) $db->query('SELECT COUNT(*) FROM contacts')->fetchColumn(),
            'projects'      => (int) $db->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
            'tasks'         => (int) $db->query('SELECT COUNT(*) FROM tasks')->fetchColumn(),
            'tasks_pending' => (int) $db->query(
                "SELECT COUNT(*) FROM tasks WHERE status IN ('pending','in_progress')"
            )->fetchColumn(),
        ];

        $recentTasks = $db->query(
            "SELECT t.id, t.title, t.status, t.priority, t.due_date,
                    c.name  AS contact_name, c.id AS contact_id,
                    p.name  AS project_name, p.id AS project_id
             FROM   tasks t
             LEFT JOIN contacts c ON c.id = t.contact_id
             LEFT JOIN projects p ON p.id = t.project_id
             ORDER BY t.created_at DESC
             LIMIT 5"
        )->fetchAll();

        $recentContacts = $db->query(
            'SELECT id, name, email, company, created_at
             FROM   contacts
             ORDER BY created_at DESC
             LIMIT 5'
        )->fetchAll();

        $this->render('home.dashboard', [
            'pageTitle'      => __('dashboard.title'),
            'counts'         => $counts,
            'recentTasks'    => $recentTasks,
            'recentContacts' => $recentContacts,
        ]);
    }
}
