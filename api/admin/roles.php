<?php
// ================================================================
// api/admin/roles.php — Admin Roles & Permissions API
// Rise & Shine Beauty AI Platform
// ================================================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/auth.php';

// Protect API
$admin = getAdmin();
if (!$admin) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $roleId = param('role_id', '', 'get');
        if ($roleId) {
            // Get single role permissions
            if ($roleId === 'GUEST') {
                jsonResponse(['success' => true, 'permissions' => []]);
            }
            $permissions = dbQuery("SELECT id, role, permissionKey, permissionLabel, allowed, isSystemLocked FROM role_permission WHERE role = ? ORDER BY permissionLabel ASC", [$roleId]);
            
            $formatted = [];
            foreach ($permissions as $p) {
                $formatted[] = [
                    'role_permission_id' => $p['id'],
                    'role_permission_role' => $p['role'],
                    'role_permission_key' => $p['permissionKey'],
                    'role_permission_label' => $p['permissionLabel'],
                    'role_permission_allowed' => (bool)$p['allowed'],
                    'role_permission_is_system_locked' => (bool)$p['isSystemLocked']
                ];
            }
            jsonResponse(['success' => true, 'permissions' => $formatted]);
        } else {
            // Get summaries
            $allPermissions = dbQuery("SELECT role, allowed FROM role_permission");
            $adminPerms = array_filter($allPermissions, function($p) { return $p['role'] === 'ADMIN'; });
            $userPerms = array_filter($allPermissions, function($p) { return $p['role'] === 'USER'; });

            $adminActive = count(array_filter($adminPerms, function($p) { return (int)$p['allowed'] === 1; }));
            $userActive = count(array_filter($userPerms, function($p) { return (int)$p['allowed'] === 1; }));

            $summaries = [
                [
                    'role_id' => 'ADMIN',
                    'role_name' => 'ADMIN',
                    'role_badge' => 'Accès Total',
                    'role_is_system_locked' => true,
                    'role_active_permissions_count' => $adminActive,
                    'role_total_permissions_count' => count($adminPerms)
                ],
                [
                    'role_id' => 'USER',
                    'role_name' => 'USER',
                    'role_badge' => 'Personnalisable',
                    'role_is_system_locked' => false,
                    'role_active_permissions_count' => $userActive,
                    'role_total_permissions_count' => count($userPerms)
                ],
                [
                    'role_id' => 'GUEST',
                    'role_name' => 'GUEST',
                    'role_badge' => 'Lecture Seule',
                    'role_is_system_locked' => true,
                    'role_active_permissions_count' => 0,
                    'role_total_permissions_count' => 0
                ]
            ];
            jsonResponse(['success' => true, 'role_summary_list' => $summaries]);
        }
    }

    if ($method === 'POST' || $method === 'PATCH') {
        $body = getJsonBody();
        $action = $body['action'] ?? '';

        if ($action === 'restore_defaults') {
            $roleId = $body['role_id'] ?? '';
            if ($roleId !== 'USER') {
                throw new RuntimeException('Seul le rôle USER peut être restauré.');
            }
            dbExecute("UPDATE role_permission SET allowed = 0, updatedAt = NOW() WHERE role = 'USER' AND isSystemLocked = 0");
            jsonResponse(['success' => true]);
        }

        // Toggle single permission status
        $permId = $body['role_permission_id'] ?? '';
        $allowed = (bool)($body['role_permission_allowed'] ?? false);

        if (empty($permId)) {
            throw new RuntimeException('ID de la permission requis.');
        }

        $perm = dbQueryOne("SELECT * FROM role_permission WHERE id = ? LIMIT 1", [$permId]);
        if (!$perm) {
            jsonResponse(['success' => false, 'error' => 'Permission introuvable'], 404);
        }

        if ($perm['isSystemLocked']) {
            throw new RuntimeException('Cette permission est verrouillée par le système.');
        }

        dbExecute("UPDATE role_permission SET allowed = ?, updatedAt = NOW() WHERE id = ?", [$allowed ? 1 : 0, $permId]);
        jsonResponse(['success' => true]);
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/admin/roles.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
