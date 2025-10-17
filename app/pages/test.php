<?php
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/csrf.php';
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/questions.php';

if (!current_user_id()) redirect(base_url());

$pdo = db();
$stmt = $pdo->prepare('SELECT answers_json FROM responses WHERE user_id=?');
$stmt->execute([current_user_id()]);
$prev = $stmt->fetch();
$prefill = $prev ? json_decode($prev['answers_json'], true) : [];
$already = $prev ? true : false;

include __DIR__.'/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-lg-10">
    <?php if ($already): ?>
      <div class="alert alert-warning">Você já respondeu este teste. As respostas foram preenchidas. Reenviar vai sobrescrever.</div>
    <?php else: ?>
      <div class="alert alert-secondary">Leia com o estômago.<br>
Não responda para parecer bem.<br>
Responda com o que você esconde até de si.<br><br>São 16 perguntas.<br>
Sim, é desconfortável.<br>
Porque você também é.

</div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('?page=result') ?>" class="monster-form">
      <?= csrf_input() ?>
      <?php foreach ($QUESTIONS as $i => [$title, $opts]): ?>
        <section class="card bg-dark-2 border-0 shadow-sm my-3">
          <div class="card-body">
            <h5 class="fw-semibold mb-3"><?= $i ?>. <?= htmlspecialchars($title) ?></h5>
            <div class="row row-cols-1 row-cols-md-2 g-2">
              <?php foreach ($opts as $letter => $label): 
                $id = "q{$i}_{$letter}";
                $checked = (!empty($prefill["q{$i}"]) && $prefill["q{$i}"]===$letter) ? 'checked' : '';
              ?>
              <div class="col">
                <label class="form-check monster-option w-100">
                  <input class="form-check-input me-2" type="radio" name="q<?= $i ?>" id="<?= $id ?>" value="<?= $letter ?>" required <?= $checked ?>>
                  <span class="badge bg-secondary me-2"><?= $letter ?></span> <?= htmlspecialchars($label) ?>
                </label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endforeach; ?>

      <div class="d-grid my-4">
        <button class="btn btn-lg btn-primary monster-pulse">Ver meu monstro</button>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__.'/footer.php'; ?>
