<?php
session_start();
if (empty($_SESSION['customer']['nama']) || empty($_SESSION['customer']['meja'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Café Modern</title>
    <!-- Pastikan path CSS benar -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ========== Tambahan / Override Style ========== */
        .menu-page {
            padding: 2rem 0;
        }

        .filter-container {
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            padding: 10px 15px;
            border-radius: 20px;
            border: 1px solid #ccc;
            flex: 1;
            min-width: 200px;
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .filter-btn {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--primary);
            background: white;
            color: var(--primary);
            cursor: pointer;
            transition: 0.2s;
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .menu-card:hover {
            transform: translateY(-5px);
        }

        .menu-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .menu-card .info {
            padding: 15px;
        }

        .menu-card h3 {
            margin-bottom: 5px;
            color: var(--primary);
        }

        .menu-card p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .price {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary);
            display: block;
            margin-bottom: 8px;
        }

        .status {
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .status.tersedia {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status.habis {
            background: #ffebee;
            color: #c62828;
        }

        .btn-tambah {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: 0.2s;
        }

        .btn-tambah:hover {
            background: #5a3e2b;
        }

        .btn-tambah:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* ========== Modal ========== */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .modal-content h3 {
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .modal-content img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .addon-group {
            margin: 15px 0;
        }

        .addon-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .addon-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .addon-option {
            display: flex;
            align-items: center;
            gap: 5px;
            background: #f5f5f5;
            padding: 5px 12px;
            border-radius: 20px;
            cursor: pointer;
            user-select: none;
        }

        .addon-option input {
            margin: 0;
        }

        .qty-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }

        .qty-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            background: var(--primary);
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
        }

        #modal-catatan {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }

        .btn-tambah-keranjang {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
        }

        .btn-tambah-keranjang:hover {
            background: #5a3e2b;
        }

        /* ========== Floating Cart ========== */
        .floating-cart {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: var(--primary);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 1500;
            text-decoration: none;
            transition: transform 0.2s;
        }

        .floating-cart.show {
            display: flex;
        }

        .floating-cart:hover {
            transform: scale(1.1);
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #d9534f;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">☕ Café Modern</a>
            <ul class="nav-links" id="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php" class="active">Menu</a></li>
                <li><a href="cart.php">🛒 <span id="cart-count">0</span></a></li>
                <li><a href="logout_customer.php" style="color:#ffb3b3;" title="Ganti Meja">🚪 Ganti Meja</a></li>
            </ul>
            <div class="hamburger" id="hamburger">☰</div>
        </div>
    </nav>

    <!-- Menu Section -->
    <section class="menu-page">
        <div class="container">
            <h2 class="section-title">Menu Kami</h2>
            <div class="filter-container">
                <input type="text" id="search" placeholder="Cari menu..." class="search-input">
                <div class="category-filters" id="category-filters"></div>
            </div>
            <div id="menu-grid" class="menu-grid"></div>
        </div>
    </section>

    <!-- Modal Detail Menu & Add‑ons -->
    <div class="modal-overlay" id="modal-addon">
        <div class="modal-content">
            <span class="modal-close" id="modal-close">&times;</span>
            <div id="modal-body"></div>
        </div>
    </div>

    <!-- Floating Cart -->
    <a href="cart.php" class="floating-cart" id="floating-cart">
        🛒
        <span class="cart-badge" id="floating-cart-count">0</span>
    </a>

    <script>
        const apiMenu = '../api/menu.php';
        let currentCategory = 'all';
        let currentMenu = null;

        // Fungsi format angka
        function number_format(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Update tampilan keranjang (navbar + floating)
        function updateCartCount() {
            fetch('../api/cart.php?action=count')
                .then(res => res.json())
                .then(data => {
                    let count = data.count || 0;
                    document.getElementById('cart-count').innerText = count;
                    let floatBtn = document.getElementById('floating-cart');
                    let floatBadge = document.getElementById('floating-cart-count');
                    floatBadge.innerText = count;
                    if (count > 0) {
                        floatBtn.classList.add('show');
                    } else {
                        floatBtn.classList.remove('show');
                    }
                })
                .catch(err => console.error('Gagal update keranjang:', err));
        }

        // Load kategori
        function loadCategories() {
            fetch(apiMenu + '?action=categories')
                .then(res => res.json())
                .then(data => {
                    let html = '<button class="filter-btn active" data-cat="all">Semua</button>';
                    data.forEach(cat => {
                        html += `<button class="filter-btn" data-cat="${cat.slug}">${cat.nama}</button>`;
                    });
                    document.getElementById('category-filters').innerHTML = html;
                    attachFilterEvents();
                });
        }

        // Load menu
        function loadMenus(category = 'all', search = '') {
            let url = apiMenu + `?action=list&category=${category}&search=${encodeURIComponent(search)}`;
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    if (data.length > 0) {
                        data.forEach(menu => {
                            html += `
                            <div class="menu-card">
                                <img src="../uploads/${menu.gambar}" alt="${menu.nama}" onerror="this.onerror=null;this.src='../uploads/default.jpg'">
                                <div class="info">
                                    <h3>${menu.nama}</h3>
                                    <p>${menu.deskripsi ?? ''}</p>
                                    <span class="price">Rp ${number_format(menu.harga)}</span>
                                    <span class="status ${menu.status}">${menu.status}</span>
                                    <button class="btn-tambah" onclick="bukaModal(${menu.id})" ${menu.status === 'habis' ? 'disabled' : ''}>
                                        ${menu.status === 'habis' ? 'Habis' : '+ Pesan'}
                                    </button>
                                </div>
                            </div>`;
                        });
                    } else {
                        html = '<p>Tidak ada menu ditemukan.</p>';
                    }
                    document.getElementById('menu-grid').innerHTML = html;
                });
        }

        // Buka modal
        function bukaModal(menuId) {
            // Coba ambil dari API get, jika tidak ada fallback ke list
            fetch(apiMenu + `?action=get&id=${menuId}`)
                .then(res => res.json())
                .then(menu => {
                    if (menu.id) {
                        currentMenu = menu;
                        showModalWithData(menu);
                    } else {
                        // Fallback: ambil dari list (kurang optimal)
                        fetch(apiMenu + '?action=list')
                            .then(res => res.json())
                            .then(allMenus => {
                                let found = allMenus.find(m => m.id == menuId);
                                if (found) {
                                    currentMenu = found;
                                    showModalWithData(found);
                                } else {
                                    Swal.fire('Error', 'Menu tidak ditemukan', 'error');
                                }
                            });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Gagal memuat detail menu', 'error');
                });
        }

        function showModalWithData(menu) {
            let modalBody = document.getElementById('modal-body');
            modalBody.innerHTML = `
                <img src="../uploads/${menu.gambar}" alt="${menu.nama}" onerror="this.onerror=null;this.src='../uploads/default.jpg'">
                <h3>${menu.nama}</h3>
                <p>${menu.deskripsi ?? ''}</p>
                <p style="font-size:1.3rem; font-weight:700; color:var(--primary);">Rp ${number_format(menu.harga)}</p>
                <div class="qty-selector">
                    <button class="qty-btn" onclick="ubahQty(-1)">−</button>
                    <span id="qty-display">1</span>
                    <button class="qty-btn" onclick="ubahQty(1)">+</button>
                </div>
                <div class="addon-group">
                    <label>Add‑ons</label>
                    <div class="addon-options">
                        <label class="addon-option"><input type="checkbox" value="Extra Shot"> Extra Shot</label>
                        <label class="addon-option"><input type="checkbox" value="Whipped Cream"> Whipped Cream</label>
                        <label class="addon-option"><input type="checkbox" value="Less Sugar"> Less Sugar</label>
                        <label class="addon-option"><input type="checkbox" value="No Ice"> No Ice</label>
                        <label class="addon-option"><input type="checkbox" value="Extra Topping"> Extra Topping</label>
                    </div>
                </div>
                <div class="addon-group">
                    <label>Catatan Khusus</label>
                    <input type="text" id="modal-catatan" placeholder="Misal: tanpa gula, gelas besar, dll.">
                </div>
                <button class="btn-tambah-keranjang" onclick="tambahKeKeranjang()">Tambah ke Keranjang</button>
            `;
            document.getElementById('modal-addon').classList.add('active');
        }

        function ubahQty(delta) {
            let qtyEl = document.getElementById('qty-display');
            let qty = parseInt(qtyEl.innerText);
            qty = Math.max(1, qty + delta);
            qtyEl.innerText = qty;
        }

        function tutupModal() {
            document.getElementById('modal-addon').classList.remove('active');
        }

        function tambahKeKeranjang() {
            if (!currentMenu) return;
            let qty = parseInt(document.getElementById('qty-display').innerText);
            // Kumpulkan add‑ons yang dipilih
            let selectedAddons = Array.from(document.querySelectorAll('.addon-option input:checked'))
                .map(el => el.value);
            let catatanManual = document.getElementById('modal-catatan').value.trim();
            let finalCatatan = [...selectedAddons, catatanManual].filter(Boolean).join(', ');

            fetch('../api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=add&menu_id=${currentMenu.id}&quantity=${qty}&catatan=${encodeURIComponent(finalCatatan)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Ditambahkan!',
                            timer: 1000,
                            showConfirmButton: false
                        });
                        updateCartCount();
                        tutupModal();
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Gagal menambahkan ke keranjang', 'error');
                });
        }

        // Filter events
        function attachFilterEvents() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentCategory = this.dataset.cat;
                    loadMenus(currentCategory, document.getElementById('search').value);
                });
            });
        }

        // Event search
        document.getElementById('search').addEventListener('keyup', function() {
            loadMenus(currentCategory, this.value);
        });

        // Event modal close
        document.getElementById('modal-close').addEventListener('click', tutupModal);
        document.getElementById('modal-addon').addEventListener('click', function(e) {
            if (e.target === this) tutupModal();
        });

        // Hamburger
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('active');
        });

        // Init
        loadCategories();
        loadMenus();
        updateCartCount();
    </script>
</body>

</html>