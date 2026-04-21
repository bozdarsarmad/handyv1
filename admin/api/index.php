<?php
require_once __DIR__ . '/../../api/config.php';

$method  = $_SERVER['REQUEST_METHOD'];
$section = $_GET['section'] ?? '';
$action  = $_GET['action'] ?? 'list';
$db = getDB();

// Check auth for all sections except users (handled after login)
if ($section !== 'users') {
    $admin = requireAdmin();
}

switch ($section) {
    // =================== DASHBOARD ===================
    case 'dashboard':
        $stats = [
            'total_orders'    => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'pending_orders'  => $db->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'")->fetchColumn(),
            'paid_orders'     => $db->query("SELECT COUNT(*) FROM orders WHERE payment_status='paid'")->fetchColumn(),
            'total_users'     => $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
            'total_revenue'   => $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status='paid'")->fetchColumn(),
        ];
        respondSuccess(['stats' => $stats]);
        break;

    // =================== USERS ===================
    case 'users':
        if ($action === 'list') {
            $rows = $db->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
            respondSuccess(['data' => $rows]);
        } elseif ($action === 'delete' && $method === 'POST') {
            $id = json_decode(file_get_contents('php://input'), true)['id'] ?? null;
            if (!$id) respondError('ID required');
            $db->prepare("DELETE FROM users WHERE id=? AND role='user'")->execute([$id]);
            respondSuccess(['data' => []], 'User deleted');
        }
        break;

    // =================== SLIDERS ===================
    case 'sliders':
        if ($action === 'list') {
            $rows = $db->query("SELECT * FROM sliders ORDER BY sort_order ASC")->fetchAll();
            respondSuccess(['data' => $rows]);
        } elseif ($action === 'save' && $method === 'POST') {
            $id    = $_POST['id'] ?? null;
            $title = trim($_POST['title'] ?? '');
            $sub   = trim($_POST['subtitle'] ?? '');
            $sort  = (int)($_POST['sort_order'] ?? 0);
            $active = (int)($_POST['is_active'] ?? 1);
            $imgUrl = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $file = $_FILES['image'];
                $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fn   = 'slide_' . time() . '.' . $ext;
                $dir  = UPLOAD_PATH . 'sliders/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($file['tmp_name'], $dir . $fn);
                $imgUrl = UPLOAD_URL . 'sliders/' . $fn;
            }
            if ($id) {
                if ($imgUrl) {
                    $db->prepare("UPDATE sliders SET title=?,subtitle=?,sort_order=?,is_active=?,image=? WHERE id=?")->execute([$title,$sub,$sort,$active,$imgUrl,$id]);
                } else {
                    $db->prepare("UPDATE sliders SET title=?,subtitle=?,sort_order=?,is_active=? WHERE id=?")->execute([$title,$sub,$sort,$active,$id]);
                }
            } else {
                if (!$imgUrl) respondError('Image required for new slider');
                $db->prepare("INSERT INTO sliders (image,title,subtitle,sort_order,is_active) VALUES (?,?,?,?,?)")->execute([$imgUrl,$title,$sub,$sort,$active]);
            }
            respondSuccess([], 'Slider saved');
        } elseif ($action === 'delete' && $method === 'POST') {
            $id = json_decode(file_get_contents('php://input'), true)['id'] ?? null;
            if (!$id) respondError('ID required');
            $db->prepare("DELETE FROM sliders WHERE id=?")->execute([$id]);
            respondSuccess([], 'Deleted');
        }
        break;

    // =================== STATS ===================
    case 'stats':
        if ($action === 'list') {
            $rows = $db->query("SELECT * FROM stats ORDER BY sort_order ASC")->fetchAll();
            respondSuccess(['data' => $rows]);
        } elseif ($action === 'save' && $method === 'POST') {
            $d = json_decode(file_get_contents('php://input'), true);
            $id = $d['id'] ?? null;
            if ($id) {
                $db->prepare("UPDATE stats SET icon=?,label=?,value=?,sort_order=?,is_active=? WHERE id=?")->execute([$d['icon'],$d['label'],$d['value'],$d['sort_order']??0,$d['is_active']??1,$id]);
            } else {
                $db->prepare("INSERT INTO stats (icon,label,value,sort_order,is_active) VALUES (?,?,?,?,?)")->execute([$d['icon'],$d['label'],$d['value'],$d['sort_order']??0,$d['is_active']??1]);
            }
            respondSuccess([], 'Stat saved');
        } elseif ($action === 'delete' && $method === 'POST') {
            $id = json_decode(file_get_contents('php://input'), true)['id'] ?? null;
            $db->prepare("DELETE FROM stats WHERE id=?")->execute([$id]);
            respondSuccess([], 'Deleted');
        }
        break;

    // =================== CATEGORIES ===================
    case 'categories':
        if ($action === 'list') {
            $rows = $db->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();
            respondSuccess(['data' => $rows]);
        } elseif ($action === 'save' && $method === 'POST') {
            $d = json_decode(file_get_contents('php://input'), true);
            $id = $d['id'] ?? null;
            if ($id) {
                $db->prepare("UPDATE categories SET name=?,sort_order=?,is_active=? WHERE id=?")->execute([$d['name'],$d['sort_order']??0,$d['is_active']??1,$id]);
            } else {
                $db->prepare("INSERT INTO categories (name,sort_order,is_active) VALUES (?,?,?)")->execute([$d['name'],$d['sort_order']??0,$d['is_active']??1]);
            }
            respondSuccess([], 'Category saved');
        } elseif ($action === 'delete' && $method === 'POST') {
            $id = json_decode(file_get_contents('php://input'), true)['id'] ?? null;
            $db->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
            respondSuccess([], 'Deleted');
        }
        break;

    // =================== CARDS ===================
    case 'cards':
        if ($action === 'list') {
            $rows = $db->query("SELECT c.*, cat.name AS category_name FROM cards c LEFT JOIN categories cat ON c.category_id=cat.id ORDER BY c.sort_order ASC")->fetchAll();
            foreach ($rows as &$r) $r['prices'] = json_decode($r['prices'], true);
            respond($rows);
        } elseif ($action === 'save' && $method === 'POST') {
            $id      = $_POST['id'] ?? null;
            $catId   = $_POST['category_id'] ?? null;
            $title   = trim($_POST['title'] ?? '');
            $desc    = trim($_POST['description'] ?? '');
            $prices  = $_POST['prices'] ?? '{}';
            $sort    = (int)($_POST['sort_order'] ?? 0);
            $active  = (int)($_POST['is_active'] ?? 1);
            if (!is_array(json_decode($prices, true))) $prices = '{}';
            $imgUrl = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $file = $_FILES['image'];
                $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fn   = 'card_' . time() . '.' . $ext;
                $dir  = UPLOAD_PATH . 'sliders/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($file['tmp_name'], $dir . $fn);
                $imgUrl = UPLOAD_URL . 'sliders/' . $fn;
            }
            if ($id) {
                if ($imgUrl) {
                    $db->prepare("UPDATE cards SET category_id=?,title=?,description=?,prices=?,sort_order=?,is_active=?,image=? WHERE id=?")->execute([$catId,$title,$desc,$prices,$sort,$active,$imgUrl,$id]);
                } else {
                    $db->prepare("UPDATE cards SET category_id=?,title=?,description=?,prices=?,sort_order=?,is_active=? WHERE id=?")->execute([$catId,$title,$desc,$prices,$sort,$active,$id]);
                }
            } else {
                $db->prepare("INSERT INTO cards (category_id,title,image,description,prices,sort_order,is_active) VALUES (?,?,?,?,?,?,?)")->execute([$catId,$imgUrl,$title,$desc,$prices,$sort,$active]);
            }
            respondSuccess([], 'Card saved');
        } elseif ($action === 'delete' && $method === 'POST') {
            $id = json_decode(file_get_contents('php://input'), true)['id'] ?? null;
            $db->prepare("DELETE FROM cards WHERE id=?")->execute([$id]);
            respondSuccess([], 'Deleted');
        }
        break;

    // =================== ORDER FIELDS ===================
    case 'fields':
        if ($action === 'list') {
            $rows = $db->query("SELECT * FROM order_fields ORDER BY section, sort_order ASC")->fetchAll();
            foreach ($rows as &$r) if ($r['options']) $r['options'] = json_decode($r['options'], true);
            respond($rows);
        } elseif ($action === 'save' && $method === 'POST') {
            $d = json_decode(file_get_contents('php://input'), true);
            $id = $d['id'] ?? null;
            $opts = isset($d['options']) ? json_encode($d['options']) : null;
            if ($id) {
                $db->prepare("UPDATE order_fields SET section=?,field_type=?,label=?,field_name=?,placeholder=?,options=?,is_required=?,sort_order=?,is_active=? WHERE id=?")->execute([$d['section'],$d['field_type'],$d['label'],$d['field_name'],$d['placeholder']??null,$opts,$d['is_required']??0,$d['sort_order']??0,$d['is_active']??1,$id]);
            } else {
                $db->prepare("INSERT INTO order_fields (section,field_type,label,field_name,placeholder,options,is_required,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?,?)")->execute([$d['section'],$d['field_type'],$d['label'],$d['field_name'],$d['placeholder']??null,$opts,$d['is_required']??0,$d['sort_order']??0,$d['is_active']??1]);
            }
            respondSuccess([], 'Field saved');
        } elseif ($action === 'delete' && $method === 'POST') {
            $id = json_decode(file_get_contents('php://input'), true)['id'] ?? null;
            $db->prepare("DELETE FROM order_fields WHERE id=?")->execute([$id]);
            respondSuccess([], 'Deleted');
        }
        break;

    // =================== PAYMENT METHODS ===================
    case 'payment_methods':
        if ($action === 'list') {
            $rows = $db->query("SELECT * FROM payment_methods ORDER BY sort_order ASC")->fetchAll();
            respondSuccess(['data' => $rows]);
        } elseif ($action === 'save' && $method === 'POST') {
            $d = json_decode(file_get_contents('php://input'), true);
            $id = $d['id'] ?? null;
            if ($id) {
                $db->prepare("UPDATE payment_methods SET name=?,type=?,account_number=?,account_title=?,instructions=?,logo_icon=?,is_active=?,sort_order=? WHERE id=?")->execute([$d['name'],$d['type'],$d['account_number']??null,$d['account_title']??null,$d['instructions']??null,$d['logo_icon']??'💳',$d['is_active']??1,$d['sort_order']??0,$id]);
            } else {
                $db->prepare("INSERT INTO payment_methods (name,type,account_number,account_title,instructions,logo_icon,is_active,sort_order) VALUES (?,?,?,?,?,?,?)")->execute([$d['name'],$d['type'],$d['account_number']??null,$d['account_title']??null,$d['instructions']??null,$d['logo_icon']??'💳',$d['is_active']??1,$d['sort_order']??0]);
            }
            respondSuccess([], 'Payment method saved');
        } elseif ($action === 'delete' && $method === 'POST') {
            $id = json_decode(file_get_contents('php://input'), true)['id'] ?? null;
            $db->prepare("DELETE FROM payment_methods WHERE id=?")->execute([$id]);
            respondSuccess([], 'Deleted');
        }
        break;

    // =================== ORDERS ===================
    case 'orders':
        if ($action === 'list') {
            $status = $_GET['status'] ?? null;
            if ($status) {
                $stmt = $db->prepare("SELECT o.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.order_status=? ORDER BY o.created_at DESC");
                $stmt->execute([$status]);
            } else {
                $stmt = $db->query("SELECT o.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC");
            }
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) {
                $r['selected_certificates'] = json_decode($r['selected_certificates'], true);
                $r['shipping_info'] = json_decode($r['shipping_info'], true);
                $r['academic_info']  = json_decode($r['academic_info'], true);
            }
            respondSuccess(['data' => $rows]);
        } elseif ($action === 'update_status' && $method === 'POST') {
            $d = json_decode(file_get_contents('php://input'), true);
            $db->prepare("UPDATE orders SET order_status=?, payment_status=? WHERE id=?")->execute([$d['order_status'],$d['payment_status'],$d['id']]);
            respondSuccess([], 'Order updated');
        } elseif ($action === 'verify_payment' && $method === 'POST') {
            $d = json_decode(file_get_contents('php://input'), true);
            $db->prepare("UPDATE payments SET status=? WHERE id=?")->execute([$d['status'],$d['id']]);
            if ($d['status'] === 'verified') {
                $db->prepare("UPDATE orders SET payment_status='paid', order_status='processing' WHERE id=?")->execute([$d['order_id']]);
            }
            respondSuccess([], 'Payment status updated');
        }
        break;

    // =================== SHIPPING RATES ===================
    case 'shipping':
        if ($action === 'list') {
            $rows = $db->query("SELECT * FROM shipping_rates ORDER BY city ASC")->fetchAll();
            respondSuccess(['data' => $rows]);
        } elseif ($action === 'save' && $method === 'POST') {
            $d = json_decode(file_get_contents('php://input'), true);
            $id = $d['id'] ?? null;
            if ($id) {
                $db->prepare("UPDATE shipping_rates SET city=?,rate=?,is_active=? WHERE id=?")->execute([$d['city'],$d['rate'],$d['is_active']??1,$id]);
            } else {
                $db->prepare("INSERT INTO shipping_rates (city,rate,is_active) VALUES (?,?,?)")->execute([$d['city'],$d['rate'],$d['is_active']??1]);
            }
            respondSuccess([], 'Rate saved');
        } elseif ($action === 'delete' && $method === 'POST') {
            $id = json_decode(file_get_contents('php://input'), true)['id'] ?? null;
            $db->prepare("DELETE FROM shipping_rates WHERE id=?")->execute([$id]);
            respondSuccess([], 'Deleted');
        }
        break;

    default:
        respondError('Invalid section', 404);
}