<?php
require_once "../config/db.php";

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $stmt = $conn->prepare("
        SELECT * FROM notelist 
        WHERE is_deleted = 0 AND (title LIKE :search OR description LIKE :search OR username LIKE :search OR created_at LIKE :search)
        ORDER BY pined DESC, created_at DESC
    ");
    $stmt->execute([':search' => "%$search%"]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    function makeLinksClickable($text)
{
    $text = htmlspecialchars($text);

    // Regex to match [emoji][link] or just [link]
    return nl2br(preg_replace_callback(
        '/(?:(\X)(?=https?:\/\/)|)(https?:\/\/[^\s]+)/u',
        function ($matches) {
            $emoji = $matches[1] ?? '';
            $url = $matches[2];

            // If there's an emoji before the URL, use it as the link text
            $linkText = $emoji !== '' ? $emoji : $url;

            return '<a class="text-decoration-none" href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $linkText . '</a>';
        },
        $text
    ));
}

foreach ($notes as $row):
    $descId = "desc-" . $row['id'];
?>
    <div class="card card-body rounded-3 shadow-sm mb-3 note-card" style="cursor:pointer;" data-target="#<?= $descId ?>">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0"><?= htmlspecialchars($row['title']) ?></h5>
            <span class="star <?= $row['pined'] ? 'active' : '' ?>"
                  data-id="<?= $row['id'] ?>"
                  data-pined="<?= $row['pined'] ?>">★</span>
        </div>
        <div class="d-flex text-break">
            <p class="m-0 truncate-2-lines" id="<?= $descId ?>"><?= makeLinksClickable($row['description']) ?></p>
        </div>
        <div class="d-flex justify-content-between align-items-end mt-2">
            <p class="m-0"><?= date("d/m/y, H:i", strtotime($row['created_at'])) ?> | noted by <?= htmlspecialchars($row['username']) ?></p>
            <div class="d-flex">
                <button type="button" class="btn btn-outline-warning editBtn"
                        data-bs-toggle="modal"
                        data-bs-target="#exampleModal"
                        data-id="<?= $row['id'] ?>"
                        data-title="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>"
                        data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>"
                        data-pined="<?= $row['pined'] ?>">
                    แก้ไข
                </button>
                <button type="button" class="btn btn-outline-danger ms-3 deleteBtn" data-id="<?= $row['id'] ?>">ลบ</button>
            </div>
        </div>
    </div>
<?php endforeach;

} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>