<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Lang;
use App\Models\User;

class ProfileController extends Controller
{
    private User  $model;
    private array $languages = ['en-US' => 'English', 'de-CH' => 'Schweizerdeutsch'];

    public function __construct()
    {
        $this->model = new User();
    }

    public function edit(): void
    {
        $user = $this->model->find((int) $_SESSION['user_id']);
        $this->render('profile.edit', [
            'pageTitle' => __('profile.settings'),
            'user'      => $user,
            'languages' => $this->languages,
            'errors'    => [],
        ]);
    }

    public function update(): void
    {
        $userId = (int) $_SESSION['user_id'];
        $user   = $this->model->find($userId);
        $errors = [];

        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $language = trim($_POST['language'] ?? 'en-US');
        $pwNew    = trim($_POST['password_new']     ?? '');
        $pwConfirm = trim($_POST['password_confirm'] ?? '');
        $pwCurrent = trim($_POST['password_current'] ?? '');

        if (empty($name))  $errors['name']  = 'Name is required.';
        if (empty($email)) $errors['email'] = 'Email is required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email.';
        elseif ($this->model->emailExists($email, $userId)) $errors['email'] = 'Email already in use.';

        if (!empty($pwNew)) {
            if (!password_verify($pwCurrent, $user['password'])) {
                $errors['password_current'] = 'Current password is incorrect.';
            } elseif (strlen($pwNew) < 8) {
                $errors['password_new'] = 'New password must be at least 8 characters.';
            } elseif ($pwNew !== $pwConfirm) {
                $errors['password_confirm'] = 'Passwords do not match.';
            }
        }

        // Handle avatar upload
        $avatarFilename = $user['avatar'];
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $result = $this->uploadAvatar($_FILES['avatar'], $userId);
            if (isset($result['error'])) {
                $errors['avatar'] = $result['error'];
            } else {
                // Remove old avatar file
                if ($avatarFilename) {
                    $old = ROOT_PATH . '/uploads/avatars/' . $avatarFilename;
                    if (file_exists($old)) unlink($old);
                }
                $avatarFilename = $result['filename'];
                $this->model->updateAvatar($userId, $avatarFilename);
                $_SESSION['user_avatar'] = $avatarFilename;
            }
        }

        if (!empty($errors)) {
            $this->render('profile.edit', [
                'pageTitle' => __('profile.settings'),
                'user'      => array_merge($user, ['name' => $name, 'email' => $email, 'language' => $language]),
                'languages' => $this->languages,
                'errors'    => $errors,
            ]);
            return;
        }

        $this->model->update($userId, [
            'name'      => $name,
            'email'     => $email,
            'role'      => $user['role'],
            'language'  => $language,
            'is_active' => 1,
        ]);

        if (!empty($pwNew)) {
            $this->model->updatePassword($userId, $pwNew);
        }

        // Update session values
        $_SESSION['user_name'] = $name;
        $_SESSION['locale']    = $language;
        Lang::setLocale($language);

        $_SESSION['flash_success'] = __('profile.saved');
        $this->redirect('/profile');
    }

    private function uploadAvatar(array $file, int $userId): array
    {
        $allowed   = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxBytes  = 2 * 1024 * 1024; // 2 MB
        $uploadDir = ROOT_PATH . '/uploads/avatars/';

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload failed. Please try again.'];
        }
        if ($file['size'] > $maxBytes) {
            return ['error' => 'Image must be under 2 MB.'];
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed, true)) {
            return ['error' => 'Only JPEG, PNG, GIF and WebP images are allowed.'];
        }

        $ext      = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
        $filename = 'user_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return ['error' => 'Could not save the image. Check folder permissions.'];
        }

        return ['filename' => $filename];
    }
}
