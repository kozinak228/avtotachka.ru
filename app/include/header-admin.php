<!-- MATRIX THEME STYLES -->
<style>
    /* Global Dark Matrix Reset */
    body {
        background: #000 !important;
        animation: none !important;
        color: #0f0 !important;
        font-family: 'Courier New', Courier, monospace !important;
        position: relative;
        min-height: 100vh;
    }

    /* Override Bootstrap defaults for Admin */
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    a,
    p,
    span,
    td,
    th,
    label {
        color: #0f0 !important;
        text-shadow: 0 0 5px rgba(0, 255, 0, 0.5);
    }

    a:hover {
        color: #fff !important;
        text-shadow: 0 0 10px #0f0;
    }

    /* Transparent panels */
    .container {
        position: relative;
        z-index: 10;
    }

    header.container-fluid {
        background: rgba(0, 20, 0, 0.8) !important;
        border-bottom: 2px solid #0f0;
        box-shadow: 0 0 15px #0f0;
    }

    .sidebar {
        background: rgba(0, 20, 0, 0.7) !important;
        border-right: 2px solid #0f0;
    }

    /* Sidebar Links - text black */
    .sidebar ul li a {
        color: #000 !important;
        text-shadow: none !important;
        font-weight: bold;
        background: rgba(255, 255, 255, 0.9) !important;
        margin: 5px 10px;
        border-radius: 6px;
        padding: 14px 18px !important;
        display: block;
        border: 1px solid rgba(0, 255, 0, 0.3);
    }
    .sidebar ul li a:hover {
        color: #000 !important;
        text-shadow: none !important;
        background: #fff !important;
        box-shadow: 0 0 15px rgba(0, 255, 0, 0.5);
    }
    
    .table {
        background: rgba(0, 0, 0, 0.6) !important;
        border: 1px solid #0f0;
    }

    .table th,
    .table td {
        border-color: #0f0 !important;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 255, 0, 0.2) !important;
    }
    
    /* Pagination (цифры внизу) - text black */
    .pagination .page-link {
        color: #000 !important;
        background-color: #0f0 !important;
        border-color: #000 !important;
        text-shadow: none !important;
        font-weight: bold;
        background: rgba(255, 255, 255, 0.9) !important;
        margin: 5px 10px;
        border-radius: 6px;
        padding: 14px 18px !important;
        display: block;
        border: 1px solid rgba(0, 255, 0, 0.3);
    }
    .pagination .page-item.active .page-link, 
    .pagination .page-link:hover {
        background-color: #fff !important;
        color: #000 !important;
        box-shadow: 0 0 10px #0f0;
    }

    /* Forms & Inputs */
    .form-control,
    .form-select {
        background-color: rgba(0, 20, 0, 0.8) !important;
        border: 1px solid #0f0 !important;
        color: #0f0 !important;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: 0 0 10px #0f0 !important;
    }

    /* Buttons */
    .btn {
        background: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid #0f0 !important;
        color: #000 !important;
        text-shadow: none !important;
        box-shadow: 0 0 5px rgba(0, 255, 0, 0.3);
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background: #0f0 !important;
        color: #000 !important;
        box-shadow: 0 0 15px #0f0;
    }

    .btn-success,
    .btn-primary,
    .btn-warning,
    .btn-danger,
    .btn-info {
        background: rgba(255, 255, 255, 0.9) !important; color: #000 !important; text-shadow: none !important;
    }


    /* CKEditor dark transparent theme */
    .ck.ck-editor__main>.ck-editor__editable {
        background: rgba(0, 20, 0, 0.8) !important;
        color: #0f0 !important;
        border-color: #0f0 !important;
        min-height: 120px;
    }
    .ck.ck-toolbar {
        background: rgba(0, 30, 0, 0.9) !important;
        border-color: #0f0 !important;
    }
    .ck.ck-toolbar .ck-button,
    .ck.ck-toolbar .ck-dropdown__button {
        color: #0f0 !important;
        background: transparent !important;
    }
    .ck.ck-toolbar .ck-button:hover {
        background: rgba(0, 255, 0, 0.2) !important;
    }
    .ck.ck-editor {
        border: 1px solid #0f0 !important;
    }
    .ck.ck-editor__editable:focus {
        box-shadow: 0 0 10px #0f0 !important;
    }
    /* Add-post form area - dark transparent */
    .add-post {
        background: rgba(0, 10, 0, 0.6) !important;
        border-radius: 8px;
        padding: 20px !important;
        border: 1px solid rgba(0, 255, 0, 0.3);
    }

    /* Matrix Canvas */
    #matrixCanvas {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -1;
        pointer-events: none;
        opacity: 0.6;
    }
</style>

<canvas id="matrixCanvas"></canvas>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const canvas = document.getElementById('matrixCanvas');
        const ctx = canvas.getContext('2d');

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%^&*()';
        const matrix = letters.split('');
        const fontSize = 16;
        const columns = canvas.width / fontSize;

        const drops = [];
        for (let x = 0; x < columns; x++) {
            drops[x] = 1;
        }

        function draw() {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#0F0';
            ctx.font = fontSize + 'px monospace';

            for (let i = 0; i < drops.length; i++) {
                const text = matrix[Math.floor(Math.random() * matrix.length)];
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);

                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }
                drops[i]++;
            }
        }

        setInterval(draw, 33);

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    });
</script>

<header class="container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-4">
                <h1>
                    <a href="<?php echo BASE_URL ?>"><i class="fas fa-car"></i> ChinaCars</a>
                </h1>
            </div>
            <nav class="col-8">
                <ul>
                    <li>
                        <a href="<?php echo BASE_URL . "profile.php"; ?>">
                            <i class="fa fa-home"></i> Личный кабинет
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fa fa-user"></i>
                            <?php echo $_SESSION['login'] ?? $_SESSION['username'] ?? 'Admin'; ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL . "logout.php"; ?>">Выход</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>