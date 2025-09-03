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

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

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
            transition: max-height 0.3s ease;
            /* Large enough to fit the content */
        }

        #editor img {
            max-width: 100%;
            width: 500px;
            height: auto;
        }

        .post-content img {
            max-width: 100%;
            width: 500px;
            height: auto;
        }

        .post-content .ql-align-center {
            text-align: center;
        }

        .post-content .ql-align-right {
            text-align: right;
        }

        .post-content .ql-align-justify {
            text-align: justify;
        }

        .chip {
            background: #e0f7fa;
            padding: 6px 10px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
        }

        .chip .btn-close {
            margin-left: 6px;
            font-size: 10px;
        }

        .ui-autocomplete {
            z-index: 1055 !important;
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
            <button type="button" class="btn btn-success mb-3 " data-bs-toggle="modal" data-bs-target="#exampleModal">สร้างโน้ต</button>
        </div>
        <input type="text" class="form-control mb-3" id="search-input" placeholder="🔍คำค้นหา">

        <?php
        $stmtCat = $conn->query("
SELECT 
    c.id, 
    c.category_name, 
    COUNT(CASE WHEN n.is_deleted = 0 THEN n.id END) AS note_count
FROM category_note c
LEFT JOIN notelist_category nc ON c.id = nc.category_id
LEFT JOIN notelist n ON nc.note_id = n.id
GROUP BY c.id, c.category_name
ORDER BY note_count DESC, c.category_name ASC;

");
        $allCategories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div id="category-filters" class="bg-white rounded border border-2 p-2 position-fixed" style="top: 275px; right: 1450px; z-index: 1000;">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <h5 class="m-0">หมวดหมู่</h5>
                <button class="btn btn-success p-0 px-2"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#editModal"
                    id="add-category-btn">
                    +เพิ่ม
                </button>
            </div>

            <?php foreach ($allCategories as $cat): ?>
                <div class="form-check d-flex justify-content-between mb-2">
                    <div class="d-flex">
                        <input class="form-check-input category-filter me-2"
                            type="checkbox"
                            id="cat-<?= $cat['id'] ?>"
                            value="<?= htmlspecialchars($cat['category_name']) ?>">
                        <label class="form-check-label" for="cat-<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </label>
                        <p class="ms-1 m-0 text-secondary">(<?= $cat['note_count'] ?>)</p>
                    </div>
                    <div>
                        <button type="button"
                            class="btn btn-light p-0 edit-category-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#editModal"
                            data-id="<?= $cat['id'] ?>"
                            data-name="<?= htmlspecialchars($cat['category_name']) ?>">
                            ✏️
                        </button>

                        <button type="button"
                            class="btn btn-danger px-2 py-0 rounded-pill text-white delete-category-btn"
                            data-id="<?= $cat['id'] ?>"
                            data-name="<?= htmlspecialchars($cat['category_name']) ?>">
                            -
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="post" action="system/update.php" id="page-form" class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editModalLabel">แก้ไข</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="category-id">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="category_name" id="category-name">
                            <label for="category-name">ชื่อหมวดหมู่</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="form-submit" class="btn btn-primary" name="updateCategoryName">อัพเดต</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </form>
            </div>
        </div>


        <div id="note-list"></div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
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
                            <div id="chip-wrapper" class="mb-3 d-flex flex-wrap gap-2 align-items-center border p-2 rounded" style="min-height: 42px;">
                                <input type="text" id="chip-input" class="flex-grow-1 border-0" placeholder="หมวดหมู่... (พิมพ์ชื่อหมวดหมู่ + Enter)" style="outline: none;" />
                                <input type="hidden" name="categories" id="categoriesInput">
                            </div>
                            <div id="editor" style="height: 200px; "></div>
                            <input type="hidden" name="description" id="hiddenInput">
                        </div>
                        <div class="modal-footer">
                            <input type="checkbox" class="btn-check" id="btn-check-outlined" name="pined" value="1">
                            <label class="btn btn-outline-warning" for="btn-check-outlined">✰ ปักหมุด</label>
                            <button type="submit" class="btn btn-primary" id="submitBtn" name="save_note">เพิ่มโน้ต</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'รายละเอียด...',
            modules: {
                toolbar: {
                    container: [
                        [{
                            'header': [1, 2, false]
                        }],
                        ['bold', 'italic', 'underline'],
                        ['image'],
                        ['link'],
                        [{
                            'align': []
                        }]
                    ],
                    handlers: {
                        image: imageHandler
                    }
                }
            }
        });

        function imageHandler() {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = function() {
                var file = input.files[0];
                if (/^image\//.test(file.type)) {
                    var formData = new FormData();
                    formData.append('image', file);

                    fetch('system_1/upload_img_note.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(result => {
                            let range = quill.getSelection();
                            quill.insertEmbed(range.index, 'image', result.url);
                        });
                } else {
                    console.warn('You can only upload images.');
                }
            };
        }

        document.querySelector('#noteForm').onsubmit = function() {
            document.querySelector('#hiddenInput').value = quill.root.innerHTML;
        };

        document.addEventListener('DOMContentLoaded', () => {
            const noteListEl = document.getElementById('note-list');
            const searchInput = document.getElementById('search-input');
            const categoryCheckboxes = document.querySelectorAll('.category-filter');

            function getSelectedCategories() {
                return Array.from(categoryCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
            }

            // Fetch notes with optional search query, update the note list and bind events
            function fetchNotes(query = '') {
                const selectedCats = getSelectedCategories();
                const params = new URLSearchParams();
                params.append('q', query);
                if (selectedCats.length) {
                    params.append('categories', selectedCats.join(',')); // send as CSV
                }

                return fetch('system_1/search_notes.php?' + params.toString()) // ✅ return this promise
                    .then(res => res.text())
                    .then(html => {
                        noteListEl.innerHTML = html;
                        bindEvents();
                    })
                    .catch(err => console.error('Error fetching notes:', err));
            }


            categoryCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => fetchNotes(searchInput.value));
            });

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

                        const expandedIds = getExpandedIds();

                        fetch('system_1/toggle_pined.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `id=${noteId}&pined=${newPined}`
                            })
                            .then(() => fetchNotes(searchInput.value)) // ✅ this now returns a real promise
                            .then(() => restoreExpanded(expandedIds))
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
                                fetch(`system/delete.php?noteId=${noteId}`, {
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
                        const categories = JSON.parse(btn.getAttribute('data-categories'));

                        const modalLabel = document.getElementById('exampleModalLabel');
                        const submitBtn = document.getElementById('submitBtn');
                        const noteIdInput = document.getElementById('note-id');
                        const noteForm = document.getElementById('noteForm');
                        const titleInput = document.getElementById('title');
                        const pinCheckbox = document.getElementById('btn-check-outlined');

                        chipWrapper.querySelectorAll('.chip').forEach(chip => chip.remove());
                        categories.forEach(cat => addChip(cat));

                        titleInput.value = title;

                        noteIdInput.value = id;
                        pinCheckbox.checked = pined;

                        quill.root.innerHTML = description;

                        modalLabel.textContent = "แก้ไขโน้ต";
                        submitBtn.name = "update_note";
                        submitBtn.textContent = "บันทึก";
                        noteForm.action = "system/update.php";
                    });
                });

                noteListEl.querySelectorAll('.note-card').forEach(card => {
                    card.addEventListener('click', (e) => {
                        if (e.target.closest('button') || e.target.closest('.star') || e.target.closest('.content')) return;
                        const targetId = card.getAttribute('data-target');
                        const desc = document.querySelector(targetId);
                        const arrow = card.querySelector('.ms-3');
                        if (desc) {
                            const expanded = desc.classList.toggle('truncate-expanded');
                            desc.classList.toggle('truncate-2-lines', !expanded);
                            if (arrow) arrow.textContent = expanded ? '⯅' : '⯆';
                        }
                    });
                });
            }


            function getExpandedIds() {
                return Array.from(document.querySelectorAll('.truncate-expanded'))
                    .map(desc => desc.id);
            }

            function restoreExpanded(ids) {
                ids.forEach(id => {
                    const desc = document.getElementById(id);
                    if (desc) {
                        desc.classList.add('truncate-expanded');
                        desc.classList.remove('truncate-2-lines');
                        const card = desc.closest('.note-card');
                        if (card) {
                            const arrow = card.querySelector('.ms-3');
                            if (arrow) arrow.textContent = '⯅';
                        }
                    }
                });
            }

            // Reset modal form when modal closes
            const exampleModal = document.getElementById('exampleModal');
            exampleModal.addEventListener('hidden.bs.modal', () => {
                const modalLabel = document.getElementById('exampleModalLabel');
                const submitBtn = document.getElementById('submitBtn');
                const noteIdInput = document.getElementById('note-id');
                const titleInput = document.getElementById('title');
                const pinCheckbox = document.getElementById('btn-check-outlined');

                titleInput.value = '';
                noteIdInput.value = '';
                pinCheckbox.checked = false;
                quill.root.innerHTML = '';

                modalLabel.textContent = "สร้างโน้ต";
                submitBtn.name = "save_note";
                submitBtn.textContent = "Save";
            });

            // Live search input event
            searchInput.addEventListener('input', () => fetchNotes(searchInput.value));

            // Initial load
            fetchNotes();
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script>
        const chipWrapper = document.getElementById('chip-wrapper');
        const chipInput = document.getElementById('chip-input');
        const noteForm = document.getElementById('noteForm');
        const categoriesInput = document.getElementById('categoriesInput');

        noteForm.addEventListener('submit', function() {
            const chips = chipWrapper.querySelectorAll('.chip');
            const values = Array.from(chips).map(chip => chip.textContent.trim().replace('×', '').trim());
            categoriesInput.value = values.join(','); // comma-separated
        });


        function addChip(text) {
            const chip = document.createElement('span');
            chip.className = 'chip';
            chip.innerHTML = `${text} <button type="button" class="btn-close" aria-label="Remove"></button>`;
            chip.querySelector('.btn-close').addEventListener('click', () => chip.remove());

            // Insert the chip before the input
            chipWrapper.insertBefore(chip, chipInput);
        }

        $(function() {
            function addChipAuto(text) {
                const chip = $('<span class="chip">')
                    .text(text)
                    .append(' <button type="button" class="btn-close" aria-label="Remove"></button>');
                chip.find('.btn-close').on('click', function() {
                    chip.remove();
                });
                $('#chip-input').before(chip);
            }

            $("#chip-input").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "system_1/get_categories.php",
                        dataType: "json",
                        data: {
                            q: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    addChipAuto(ui.item.value);
                    $(this).val('');
                    return false;
                }
            });

            $("#chip-input").on('keypress', function(e) {
                if (e.which === 13 && $(this).val().trim() !== '') {
                    if ($(".ui-menu-item-wrapper.ui-state-active").length) {
                        return;
                    }
                    e.preventDefault();
                    addChipAuto($(this).val().trim());
                    $(this).val('');
                }
            });


            $("#noteForm").on('submit', function() {
                const chips = $("#chip-wrapper .chip");
                const values = chips.map(function() {
                    return $(this).text().replace('×', '').trim();
                }).get();
                $("#categoriesInput").val(values.join(','));
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var editButtons = document.querySelectorAll('.edit-category-btn');
            var addButton = document.getElementById('add-category-btn');
            var modalTitle = document.getElementById('editModalLabel');
            var categoryId = document.getElementById('category-id');
            var categoryName = document.getElementById('category-name');
            var formSubmit = document.getElementById('form-submit');
            var form = document.getElementById('page-form');
            var editModal = document.getElementById('editModal');

            // For Edit
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    modalTitle.textContent = 'แก้ไขหมวดหมู่';
                    categoryId.value = this.getAttribute('data-id');
                    categoryName.value = this.getAttribute('data-name');
                    form.action = 'system/update.php';
                    formSubmit.name = "updateCategoryName";
                    formSubmit.textContent = 'อัพเดต';
                });
            });

            // For Add
            addButton.addEventListener('click', function() {
                modalTitle.textContent = 'เพิ่มหมวดหมู่';
                categoryId.value = '';
                categoryName.value = '';
                form.action = 'system/insert.php';
                formSubmit.name = "addCategoryName";
                formSubmit.textContent = 'เพิ่ม';
            });

            editModal.addEventListener('hidden.bs.modal', function() {
                modalTitle.textContent = 'เพิ่มหมวดหมู่';
                categoryId.value = '';
                categoryName.value = '';
                form.action = 'system/insert.php';
                formSubmit.name = "updateCategoryName";
                formSubmit.textContent = 'เพิ่ม';
            });

            document.querySelectorAll('.delete-category-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const categoryId = btn.getAttribute('data-id');
                    const categoryName = btn.getAttribute('data-name');

                    Swal.fire({
                        title: 'คุณแน่ใจหรือไม่?',
                        text: `คุณต้องการลบหมวดหมู่ ${categoryName} ใช่ไหม?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#aaa',
                        confirmButtonText: 'ใช่, ลบเลย!',
                        cancelButtonText: 'ยกเลิก'
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch(`system/delete.php?categoryId=${categoryId}`, {
                                    method: 'GET'
                                })
                                .then(() => location.reload())
                                .catch(err => console.error('Delete error:', err));
                        }
                    });
                });
            });
        });
    </script>


    <?php SC5() ?>
</body>

</html>