<?php
// ================================================================
// api/admin/users.php — Admin Users CRUD API
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
        $userId = param('id', '', 'get');
        if ($userId) {
            $userRecord = dbQueryOne("SELECT id, account, email, role, status, avatarUrl, displayName, registeredAt, lastLoginAt FROM account WHERE id = ? LIMIT 1", [$userId]);
            if (!$userRecord) {
                jsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
            }
            jsonResponse(['success' => true, 'user' => $userRecord]);
        } else {
            $keyword = trim(param('keyword', '', 'get'));
            $role = param('role', 'ALL', 'get');
            $status = param('status', 'ALL', 'get');
            
            $where = ["1=1"];
            $params = [];

            if (!empty($keyword)) {
                $where[] = "(account LIKE ? OR email LIKE ? OR displayName LIKE ?)";
                $params[] = "%$keyword%";
                $params[] = "%$keyword%";
                $params[] = "%$keyword%";
            }
            if ($role !== 'ALL') {
                $where[] = "role = ?";
                $params[] = $role;
            }
            if ($status !== 'ALL') {
                $where[] = "status = ?";
                $params[] = $status;
            }

            $whereSQL = implode(' AND ', $where);
            $users = dbQuery("SELECT id, account, email, role, status, displayName, registeredAt FROM account WHERE $whereSQL ORDER BY registeredAt DESC", $params);
            jsonResponse(['success' => true, 'users' => $users]);
        }
    }

    if ($method === 'POST' || $method === 'PATCH') {
        $body = getJsonBody();
        $userId = $body['userId'] ?? '';
        $action = $body['action'] ?? '';

        if (empty($userId)) {
            throw new RuntimeException('ID de l\'utilisateur requis.');
        }

        $targetUser = dbQueryOne("SELECT * FROM account WHERE id = ? LIMIT 1", [$userId]);
        if (!$targetUser) {
            jsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
        }

        if ($action === 'toggle_role') {
            $newRole = $targetUser['role'] === 'ADMIN' ? 'USER' : 'ADMIN';

            // Prevent revoking the last active admin
            if ($newRole === 'USER' && $targetUser['role'] === 'ADMIN' && $targetUser['status'] === 'ACTIVE') {
                $activeAdmins = dbQueryOne("SELECT COUNT(*) as cnt FROM account WHERE role = 'ADMIN' AND status = 'ACTIVE'")['cnt'];
                if ($activeAdmins <= 1) {
                    throw new RuntimeException('Impossible de révoquer le dernier administrateur actif du système.');
                }
            }

            dbExecute("UPDATE account SET role = ?, updatedAt = NOW() WHERE id = ?", [$newRole, $userId]);
            jsonResponse(['success' => true, 'newRole' => $newRole]);
        } 
        
        if ($action === 'toggle_status') {
            $newStatus = $targetUser['status'] === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';

            // Prevent suspending the last active admin
            if ($newStatus === 'SUSPENDED' && $targetUser['role'] === 'ADMIN' && $targetUser['status'] === 'ACTIVE') {
                $activeAdmins = dbQueryOne("SELECT COUNT(*) as cnt FROM account WHERE role = 'ADMIN' AND status = 'ACTIVE'")['cnt'];
                if ($activeAdmins <= 1) {
                    throw new RuntimeException('Impossible de suspendre le dernier administrateur actif du système.');
                }
            }

            dbExecute("UPDATE account SET status = ?, updatedAt = NOW() WHERE id = ?", [$newStatus, $userId]);
            jsonResponse(['success' => true, 'newStatus' => $newStatus]);
        }

        throw new RuntimeException('Action non prise en charge.');
    }

    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
} catch (RuntimeException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log('[api/admin/users.php] ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Erreur serveur'], 500);
}
