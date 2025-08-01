<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
date_default_timezone_set("Asia/Bangkok");

if (!isset($_SESSION["admin_log"])) {
    $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
    header("location: login.php");
    exit;
}
$admin = $_SESSION['admin_log'];
$sql = "SELECT CONCAT(fname, ' ', lname) AS full_name FROM admin WHERE username = :admin LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":admin", $admin);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$name = $result['full_name'] ?? '-';

?>
<!doctype html>
<html lang="en">

<head>
    <title>งานของฉัน | ระบบบริหารจัดการ ศูนย์บริการซ่อมคอมพิวเตอร์</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS v5.2.1 -->
    <?php bs5() ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #F9FDFF;
        }

        .star {
            font-size: 29px;
            cursor: pointer;
            color: #ddd;
            line-height: 0;
        }

        .star.active,
        .star:hover {
            color: #ffc107;
        }

        .truncate-2-lines {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-height: 3.2em;
            transition: max-height 0.3s ease;
        }

        .truncate-expanded {
            max-height: 1000px;
            transition: max-height 0.3s ease;
            /* Large enough to fit the content */
        }
    </style>
</head>

<body>
    <?php navbar() ?>
    <div class="container" style="width: 50%;">

        <?php foreach (['error' => 'danger', 'warning' => 'warning', 'success' => 'success'] as $key => $class): ?>
            <?php if (isset($_SESSION[$key])): ?>
                <div class="alert alert-<?= $class ?>" role="alert">
                    <?= htmlspecialchars($_SESSION[$key], ENT_QUOTES, 'UTF-8') ?>
                    <?php unset($_SESSION[$key]); ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <h1 class="text-center my-4">โน้ตที่บันทึกไว้</h1>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-success mb-3 " data-bs-toggle="modal" data-bs-target="#exampleModal">เพิ่มโพสต์</button>
        </div>

        <input type="text" class="form-control mb-3" id="search-input" placeholder="คำค้นหา">
        <div id="note-list"></div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">สร้างโน้ต</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="noteForm" action="system/insert.php" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="note_id" id="note-id"> <!-- for editing -->
                            <input type="hidden" name="username" value="<?php echo $_SESSION['admin_log']; ?>">

                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="title" name="title">
                                <label for="title">หัวข้อ</label>
                            </div>

                            <div class="form-floating">
                                <textarea class="form-control" id="description" name="description" style="height: 100px"></textarea>
                                <label for="description">รายละเอียด</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="checkbox" class="btn-check" id="btn-check-outlined" name="pined" value="1">
                            <label class="btn btn-outline-warning" for="btn-check-outlined">✰ ปักหมุด</label>
                            <button type="submit" class="btn btn-primary" id="submitBtn" name="save_note">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const noteListEl = document.getElementById('note-list');
            const searchInput = document.getElementById('search-input');

            // Fetch notes with optional search query, update the note list and bind events
            function fetchNotes(query = '') {
                fetch('system_1/search_notes.php?q=' + encodeURIComponent(query))
                    .then(res => res.text())
                    .then(html => {
                        noteListEl.innerHTML = html;
                        bindEvents();
                    })
                    .catch(err => console.error('Error fetching notes:', err));
            }

            // Bind events for stars, delete buttons, edit buttons
            function bindEvents() {
                // Pin toggle
                noteListEl.querySelectorAll('.star').forEach(star => {
                    star.addEventListener('click', () => {
                        const noteId = star.getAttribute('data-id');
                        const currentPined = parseInt(star.getAttribute('data-pined'));
                        const newPined = currentPined === 1 ? 0 : 1;

                        star.classList.toggle('active');
                        star.setAttribute('data-pined', newPined);

                        fetch('system_1/toggle_pined.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `id=${noteId}&pined=${newPined}`
                            })
                            .then(() => fetchNotes(searchInput.value)) // refresh list keeping current search
                            .catch(err => console.error('Toggle pin error:', err));
                    });
                });

                // Delete with SweetAlert2
                noteListEl.querySelectorAll('.deleteBtn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const noteId = btn.getAttribute('data-id');
                        Swal.fire({
                            title: 'คุณแน่ใจหรือไม่?',
                            text: "คุณต้องการลบโน้ตนี้ใช่ไหม",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'ใช่, ลบเลย!',
                            cancelButtonText: 'ยกเลิก'
                        }).then(result => {
                            if (result.isConfirmed) {
                                fetch(`system/delete.php?id=${noteId}`, {
                                        method: 'GET'
                                    })
                                    .then(() => fetchNotes(searchInput.value))
                                    .catch(err => console.error('Delete error:', err));
                            }
                        });
                    });
                });

                // Edit buttons (populate modal form)
                noteListEl.querySelectorAll('.editBtn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.getAttribute('data-id');
                        const title = btn.getAttribute('data-title');
                        const description = btn.getAttribute('data-description');
                        const pined = btn.getAttribute('data-pined') === '1';

                        const modalLabel = document.getElementById('exampleModalLabel');
                        const submitBtn = document.getElementById('submitBtn');
                        const noteIdInput = document.getElementById('note-id');
                        const noteForm = document.getElementById('noteForm');
                        const titleInput = document.getElementById('title');
                        const descriptionInput = document.getElementById('description');
                        const pinCheckbox = document.getElementById('btn-check-outlined');

                        titleInput.value = title;
                        descriptionInput.value = description;
                        noteIdInput.value = id;
                        pinCheckbox.checked = pined;

                        modalLabel.textContent = "แก้ไขโน้ต";
                        submitBtn.name = "update_note";
                        submitBtn.textContent = "Update";
                        noteForm.action = "system/update.php";
                    });
                });

                noteListEl.querySelectorAll('.note-card').forEach(card => {
                    card.addEventListener('click', (e) => {
                        if (e.target.closest('button') || e.target.closest('.star')) return;
                        const targetId = card.getAttribute('data-target');
                        const desc = document.querySelector(targetId);
                        if (desc) {
                            desc.classList.toggle('truncate-2-lines');
                            desc.classList.toggle('truncate-expanded');
                        }
                    });
                });
            }

            // Reset modal form when modal closes
            const exampleModal = document.getElementById('exampleModal');
            exampleModal.addEventListener('hidden.bs.modal', () => {
                const modalLabel = document.getElementById('exampleModalLabel');
                const submitBtn = document.getElementById('submitBtn');
                const noteIdInput = document.getElementById('note-id');
                const titleInput = document.getElementById('title');
                const descriptionInput = document.getElementById('description');
                const pinCheckbox = document.getElementById('btn-check-outlined');

                titleInput.value = '';
                descriptionInput.value = '';
                noteIdInput.value = '';
                pinCheckbox.checked = false;

                modalLabel.textContent = "สร้างโน้ต";
                submitBtn.name = "save_note";
                submitBtn.textContent = "Save";
            });

            // Live search input event
            searchInput.addEventListener('input', () => {
                fetchNotes(searchInput.value);
            });

            // Initial load
            fetchNotes();
        });
    </script>

    <?php SC5() ?>
</body>

</html>