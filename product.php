<?php
//Product Details Page
session_start();
include 'DBConn.php';

// Helper: resolve product image path (checks images/ and Uploads/ case-insensitively)
function resolve_image_path($filename) {
    if (empty($filename)) return '';
    $folders = ['images', 'Uploads'];
    foreach ($folders as $f) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . $f . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($path)) return $f . '/' . $filename;
        if (is_dir(__DIR__ . DIRECTORY_SEPARATOR . $f)) {
            $files = scandir(__DIR__ . DIRECTORY_SEPARATOR . $f);
            foreach ($files as $file) {
                if (strcasecmp($file, $filename) === 0) return $f . '/' . $file;
                $baseA = pathinfo($file, PATHINFO_FILENAME);
                $baseB = pathinfo($filename, PATHINFO_FILENAME);
                if (strcasecmp($baseA, $baseB) === 0) return $f . '/' . $file;
            }
        }
    }
    return '';
}

$product_id = $_GET['id'] ?? 0;

$sql = "SELECT p.*, c.category_name FROM tblProducts p 
        LEFT JOIN tblCategories c ON p.category_id = c.category_id 
        WHERE p.product_id = $product_id";
$product = $conn->query($sql)->fetch_assoc();

if (!$product) {
    header("Location: shop.php");
    exit();
}

$carbonSaved = round($product['price'] * 0.5, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - Pastimes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c5f2d;
            text-decoration: none;
        }
        
        .product-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .product-image {
            background: #e0e0e0;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
        }
        
        .product-details {
            padding: 2rem;
        }
        
        .product-title {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .product-brand {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-size: 2rem;
            color: #2c5f2d;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .condition-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 5px;
            font-size: 0.8rem;
            background: #e8f5e9;
            color: #2c5f2d;
        }
        
        .carbon-info {
            background: #f0f7f0;
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #2c5f2d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 1rem;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #ff6b35;
        }
        
        @media (max-width: 768px) {
            .product-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Pastimes♻️</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="cart.php">Cart</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="product-container">
        <div class="product-image">
            <?php $img = resolve_image_path($product['main_image']); ?>
            <?php if($img): ?>
                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" style="max-width:100%; height:auto;">
            <?php else: ?>
                <div style="width:100%;height:300px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;color:#999;">No image available</div>
            <?php endif; ?>
        </div>
        <div class="product-details">
            <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
            <div class="product-brand"><?php echo htmlspecialchars($product['brand']); ?> | <?php echo $product['size']; ?></div>
            <div class="product-price">R <?php echo number_format($product['price'], 2); ?></div>
            <div>
                <span class="condition-badge"><?php echo $product['condition_rating']; ?> Condition</span>
            </div>
            <div class="carbon-info">
                🌱 <strong>Estimated Carbon Savings: <?php echo $carbonSaved; ?> kg CO₂</strong><br>
                <small>By buying second-hand, you're helping reduce fashion waste!</small>
            </div>
            <p style="margin: 1rem 0;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <div>
                <form action="add_to_cart.php" method="POST" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <button type="submit" class="btn btn-primary">Add to Cart 🛒</button>
                </form>
                <button class="btn" onclick="alert('Added to wishlist!')">Add to Wishlist ❤️</button>
                <button class="btn" id="msgToggleBtn">Message Seller 💬</button>
                <div id="messageBox" style="display:none;margin-top:1rem;width:100%;">
                    <textarea id="messageText" placeholder="Write a message to the seller..." style="width:100%;height:80px;padding:0.5rem;border:1px solid #ddd;border-radius:6px;"></textarea>
                    <div style="margin-top:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                        <button class="btn" id="sendMsgBtn">Send Message</button>
                        <button class="btn" id="cancelMsgBtn" style="background:#bbb;">Cancel</button>
                        <span id="msgStatus" style="margin-left:1rem;color:#2c5f2d;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const sellerId = <?php echo (int)$product['seller_id']; ?>;
        const productId = <?php echo (int)$product['product_id']; ?>;
        const toggle = document.getElementById('msgToggleBtn');
        const box = document.getElementById('messageBox');
        const sendBtn = document.getElementById('sendMsgBtn');
        const cancelBtn = document.getElementById('cancelMsgBtn');
        const statusEl = document.getElementById('msgStatus');

        toggle.addEventListener('click', () => { box.style.display = box.style.display === 'none' ? 'block' : 'none'; });
        cancelBtn.addEventListener('click', () => { box.style.display = 'none'; statusEl.textContent = ''; document.getElementById('messageText').value = ''; });

        sendBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const text = document.getElementById('messageText').value.trim();
            if (!text) { statusEl.style.color = 'red'; statusEl.textContent = 'Please enter a message.'; return; }
            statusEl.style.color = '#2c5f2d'; statusEl.textContent = 'Sending...';
            // Create a normal POST form so the server can redirect back to this product page
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'messages_action.php';
            // hidden inputs
            const inputs = {
                action: 'send_message',
                partner_id: sellerId,
                message_text: text,
                product_id: productId,
                return_to: 'product.php?id=' + productId
            };
            for (const k in inputs) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = k;
                inp.value = inputs[k];
                form.appendChild(inp);
            }
            document.body.appendChild(form);
            form.submit();
        });
    </script>
</body>
</html>