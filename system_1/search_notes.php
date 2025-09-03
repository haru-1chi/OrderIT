<?php
require_once "../config/db.php";

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$selectedCategories = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];

try {
    $sql = "
SELECT DISTINCT n.*
FROM notelist n
LEFT JOIN notelist_category nc ON n.id = nc.note_id
LEFT JOIN category_note c ON nc.category_id = c.id
WHERE n.is_deleted = 0
  AND (
       n.title LIKE :search
    OR n.description LIKE :search
    OR n.username LIKE :search
    OR n.created_at LIKE :search
    OR c.category_name LIKE :search
  )
";

    $params = [':search' => "%$search%"];

    if (!empty($selectedCategories)) {
        $catPlaceholders = [];
        foreach ($selectedCategories as $index => $cat) {
            $key = ":cat$index";
            $catPlaceholders[] = $key;
            $params[$key] = $cat;
        }
        $sql .= " AND c.category_name IN (" . implode(',', $catPlaceholders) . ")";
    }

    $sql .= " ORDER BY n.pined DESC, n.updated_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    function convertEmojiLinks($html)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $anchors = $doc->getElementsByTagName('a');

        foreach ($anchors as $a) {
            $text = $a->textContent; // safer than nodeValue

            if (preg_match('/^\X/u', $text, $match)) {
                $emoji = $match[0];

                // Remove all children and add only emoji text node
                while ($a->firstChild) {
                    $a->removeChild($a->firstChild);
                }
                $a->appendChild($doc->createTextNode($emoji));
            }
        }

        // Extract body inner HTML
        $body = $doc->getElementsByTagName('body')->item(0);
        $innerHTML = '';
        foreach ($body->childNodes as $child) {
            $innerHTML .= $doc->saveHTML($child);
        }
        return $innerHTML;
    }

    foreach ($notes as $row):
        $descId = "desc-" . $row['id'];


        $stmtCat = $conn->prepare("
    SELECT c.category_name 
    FROM notelist_category nc
    JOIN category_note c ON nc.category_id = c.id
    WHERE nc.note_id = :note_id
");
        $stmtCat->execute([':note_id' => $row['id']]);
        $categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
        $categoriesJson = htmlspecialchars(json_encode($categories), ENT_QUOTES);
?>
        <div class="card card-body rounded-3 shadow-sm mb-3 note-card" style="cursor:pointer;" data-target="#<?= $descId ?>">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0"><?= htmlspecialchars($row['title']) ?></h5>
                <div class="d-flex align-items-center">
                    <span class="star <?= $row['pined'] ? 'active' : '' ?>"
                        data-id="<?= $row['id'] ?>"
                        data-pined="<?= $row['pined'] ?>">★</span>
                    <span class="ms-3">⯆</span>
                </div>
            </div>
            <div class="content" style="cursor:default;">
                <?php if (!empty($categories)): ?>
                    <div class="mb-2 d-flex flex-wrap gap-2">
                        <?php foreach ($categories as $cat): ?>
                            <span class="chip" data-chip="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></span>
                        <?php endforeach; ?>

                    </div>
                <?php endif; ?>
                <div class="d-flex text-break">
                    <div class="w-100 m-0 truncate-2-lines post-content" id="<?= $descId ?>">
                        <?= $row['description'] ?>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-2">
                <div>
                    <?php if (!empty($row['edited_by'])): ?>
                        <p class="m-0">
                            สร้าง&nbsp;&nbsp;&nbsp;:&nbsp; <?= date("d/m/y, H:i", strtotime($row['created_at'])) ?> | noted by <?= htmlspecialchars($row['username']) ?>
                        </p>
                        <p class="m-0">
                            อัพเดต:&nbsp; <?= date("d/m/y, H:i", strtotime($row['updated_at'])) ?> | noted by <?= htmlspecialchars($row['edited_by']) ?>
                        </p>
                    <?php else: ?>
                        <p class="m-0">
                            สร้าง: <?= date("d/m/y, H:i", strtotime($row['created_at'])) ?> | noted by <?= htmlspecialchars($row['username']) ?>
                        </p>
                    <?php endif; ?>
                </div>


                <div class="d-flex">
                    <button type="button" class="btn btn-outline-warning editBtn"
                        data-bs-toggle="modal"
                        data-bs-target="#exampleModal"
                        data-id="<?= $row['id'] ?>"
                        data-title="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>"
                        data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>"
                        data-pined="<?= $row['pined'] ?>"
                        data-categories="<?= $categoriesJson ?>">
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