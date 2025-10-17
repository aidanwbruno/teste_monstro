<?php
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/csrf.php';
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/results.php';
require_once __DIR__.'/../includes/questions.php'; 
$cfg = @require __DIR__.'/../includes/config.php';

if (!current_user_id()) redirect(base_url());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  // total de perguntas baseado no array atual
  if (!isset($QUESTIONS) || !is_array($QUESTIONS) || !$QUESTIONS) {
    http_response_code(500); exit('Banco de perguntas indisponível.');
  }
  $total = count($QUESTIONS);

  $answers = [];
  for ($i = 1; $i <= $total; $i++) {
    $k = 'q'.$i;
    $v = $_POST[$k] ?? null;
    if (!in_array($v, ['A','B','C','D','E','F','G','H'], true)) {
      http_response_code(422); exit('Respostas inválidas.');
    }
    $answers[$k] = $v;
  }

  $counts = array_fill_keys(['A','B','C','D','E','F','G','H'], 0);
  foreach ($answers as $v) $counts[$v]++;
  arsort($counts);
  $dominant = array_key_first($counts);

  $pdo = db();
  $sql = "INSERT INTO responses (user_id, answers_json, dominant_letter)
          VALUES (?, ?, ?)
          ON DUPLICATE KEY UPDATE
            answers_json = VALUES(answers_json),
            dominant_letter = VALUES(dominant_letter),
            submitted_at = NOW()";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([current_user_id(), json_encode($answers, JSON_UNESCAPED_UNICODE), $dominant]);

  $_SESSION['last_dominant'] = $dominant;
  $_SESSION['last_answers']  = $answers;
  redirect(base_url('?page=result'));
}


$dominant = $_SESSION['last_dominant'] ?? null;
$answers  = $_SESSION['last_answers'] ?? null;
if (!$dominant) {
  $pdo = db();
  $stmt = $pdo->prepare('SELECT answers_json, dominant_letter FROM responses WHERE user_id=?');
  $stmt->execute([current_user_id()]);
  if ($row = $stmt->fetch()) {
    $answers = json_decode($row['answers_json'], true);
    $dominant = $row['dominant_letter'];
  }
}

include __DIR__.'/header.php'; 
if (!$dominant) { echo '<div class="alert alert-danger">Sem resultado encontrado.</div>'; include __DIR__.'/footer.php'; exit; }

$R = $RESULTS[$dominant] ?? null;

// NEW: prepara URLs e textos para os botões
$siteUrl   = isset($cfg['site_url']) && filter_var($cfg['site_url'], FILTER_VALIDATE_URL) ? $cfg['site_url'] : base_url();
$shareUrl  = $siteUrl; // se preferir, pode usar base_url('?page=test')
$shareTitle= 'Teste — O Monstro Que Te Habita';
$shareText = 'Meu monstro dominante: ' . ($R['titulo'] ?? $dominant) . ' — ' . ($R['resumo'] ?? '') . ' Faça o teste: ' . $shareUrl;
?>
<section class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card bg-dark-2 border-0 shadow-lg monster-frame">
      <div class="card-body p-4 p-lg-5">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <center><h1 class="h3 fw-bold mb-0">Seu Monstro Dominante: <br><span class="text-gradient"><?= htmlspecialchars($R['titulo']) ?></span></h1></center>
        </div>
        <center><p class="text-secondary mt-2"><?= htmlspecialchars($R['resumo']) ?></p></center>
        <hr class="border-secondary">
        <div class="prose"><pre class="text-light" style="white-space:pre-wrap;"><?= htmlspecialchars($R['texto']) ?></pre></div>

        <div class="mt-4 d-flex flex-wrap gap-2">
          <a class="btn btn-outline-light" href="<?= base_url('?page=test') ?>">Refazer teste</a>
          <a class="btn btn-secondary" href="<?= base_url() ?>">Início</a>

          <!-- NEW: Abrir meu site -->
          <a class="btn btn-success" href="<?= htmlspecialchars($siteUrl) ?>" target="_blank" rel="noopener">
            Conheça meu trabalho
          </a>
          

        </div>
      </div>
    </div>

    <div class="card bg-dark-2 border-0 shadow-sm mt-3">
      <div class="card-body">
        <h5 class="fw-semibold">Suas marcações</h5>
        <div class="d-flex flex-wrap gap-2 mt-2">
          <?php
            $counts = array_fill_keys(['A','B','C','D','E','F','G','H'], 0);
            foreach (($answers ?? []) as $k=>$v) { $counts[$v]++; }
            foreach ($counts as $L=>$n): ?>
              <span class="badge text-bg-dark border border-secondary">
                <span class="me-1"><?= $L ?></span>
                <span class="fw-bold"><?= $n ?></span>
              </span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</section>

<?php include __DIR__.'/footer.php'; ?>
