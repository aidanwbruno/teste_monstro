<?php
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/passwords.php';
require_once __DIR__.'/../includes/csrf.php';   // <= ADICIONE/DEIXE AQUI, ANTES DE USAR csrf_check()

include __DIR__.'/header.php';

// NUNCA use require_once para pegar o array de config (ele retorna true se já tiver sido incluído).
$cfg = require __DIR__.'/../includes/config.php';

if (isset($_GET['action']) && $_GET['action']==='logout') { logout_user(); redirect(base_url()); }

$errors = [];
$mode = $_POST['mode'] ?? 'login';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check();
  $pdo = db();

  if ($mode === 'register') {
    $name = sanitize_text($name);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : '';
    $phone = preg_replace('/\D+/', '', $phone);
    if (!required($name)) $errors['name']='Nome é obrigatório';
    if (!$email) $errors['email']='E-mail inválido';
    if (!required($phone)) $errors['phone']='Telefone é obrigatório';
    if (!required($password)) $errors['password']='Senha é obrigatória';
    if (!$errors) {
      $u = $pdo->prepare('SELECT id FROM users WHERE email=?'); $u->execute([$email]);
      if ($u->fetch()) { $errors['email'] = 'E-mail já cadastrado. Faça login.'; }
      else {
        $hash = hash_password_portable($password);
if ($hash === false) {
  $errors['password'] = 'Falha ao gerar hash da senha no servidor. Tente novamente mais tarde ou contate o suporte da hospedagem.';
} else {
  $pdo->prepare('INSERT INTO users (name,email,phone,password_hash) VALUES (?,?,?,?)')
      ->execute([$name,$email,$phone,$hash]);
  login_user((int)$pdo->lastInsertId());
  redirect(base_url('?page=test'));
}

      }
    }
  }

  if ($mode === 'login') {
    $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : '';
    if (!$email) $errors['email']='E-mail inválido';
    if (!required($password)) $errors['password']='Informe sua senha';
    if (!$errors) {
      $stmt = $pdo->prepare('SELECT id,password_hash FROM users WHERE email=?');
      $stmt->execute([$email]); $user = $stmt->fetch();
      if (!$user || empty($user['password_hash']) || !verify_password_portable($password, $user['password_hash'])) {
        $errors['auth'] = 'Credenciais inválidas.';
      } else {
        login_user((int)$user['id']); redirect(base_url('?page=test'));
      }
    }
  }

  if ($mode === 'magic') {
    $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : '';
    if (!$email) $errors['email']='E-mail inválido';
    if (!$errors) {
      $stmt = $pdo->prepare('SELECT id,name,email FROM users WHERE email=?'); $stmt->execute([$email]); $user = $stmt->fetch();
      if (!$user) {
        $pdo->prepare('INSERT INTO users (name,email,phone) VALUES (?,?,?)')
            ->execute(['Convidado', $email, '']);
        $user = ['id'=>$pdo->lastInsertId(),'name'=>'Convidado','email'=>$email];
      } else {
        $user = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email']];
      }
      $token = create_magic_token((int)$user['id']);
      send_magic_link($user, $token);
      $success_magic = true;
    }
  }
}

if (!is_array($cfg)) { var_dump('CONFIG INVALIDO', $cfg); exit; }
?>
<section class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card bg-dark-2 border-0 shadow-lg monster-frame">
      <div class="card-body p-4 p-lg-5">
        <h1 class="display-6 fw-bold mb-3 blue-text">TESTE<br>O MONSTRO QUE TE HABITA</h1>
        <h2 class="display-6 fw-bold mb-3 subtitulo">(E TE DEVORA EM SILÊNCIO)</h2>
        <p class="text-secondary-emphasis">Um rito de nomeação para aquilo que te come por dentro quando ninguém está vendo.</p>
        <ul class="nav nav-pills gap-2 my-3" id="authTabs" role="tablist">
          <li class="nav-item" role="presentation"><button class="nav-link <?= $mode === 'login' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#login">Login</button></li>
          <li class="nav-item" role="presentation"><button class="nav-link <?= $mode === 'register' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#register">Registrar</button></li>
          <li class="nav-item" role="presentation"><button class="nav-link <?= $mode === 'magic' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#magic">Link mágico</button></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade <?= $mode === 'login' ? 'show active' : '' ?>" id="login">
            <form method="post" class="row g-3 mt-1">
              <?= csrf_input() ?><input type="hidden" name="mode" value="login">
              <div class="col-12">
                <label class="form-label">E-mail</label>
                <input class="form-control form-control-lg" type="email" name="email" value="<?= htmlspecialchars($email) ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Senha</label>
                <input class="form-control form-control-lg" type="password" name="password">
              </div>
              <?php if (!empty($errors['auth'])): ?><div class="text-danger"><?= $errors['auth'] ?></div><?php endif; ?>
              <div class="d-grid mt-2"><button class="btn btn-lg btn-primary button-color">Entrar</button></div>
            </form>
          </div>
          <div class="tab-pane fade <?= $mode === 'register' ? 'show active' : '' ?>" id="register">
            <form method="post" class="row g-3 mt-1">
              <?= csrf_input() ?><input type="hidden" name="mode" value="register">
              <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input class="form-control form-control-lg" name="name" value="<?= htmlspecialchars($name) ?>">
                <?php if (!empty($errors['name'])): ?><div class="invalid-feedback d-block"><?= $errors['name'] ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">E-mail</label>
                <input class="form-control form-control-lg" type="email" name="email" value="<?= htmlspecialchars($email) ?>">
                <?php if (!empty($errors['email'])): ?><div class="invalid-feedback d-block"><?= $errors['email'] ?></div><?php endif; ?>
              </div>
                <div class="col-md-6">
                    <label class="form-label">Telefone</label>
                    <input
                        class="form-control form-control-lg"
                        name="phone"
                        id="phone" type="tel" maxlength="15" value="<?= htmlspecialchars($phone) ?>"
                        onkeyup="phoneMask(this)" >
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="invalid-feedback d-block"><?= $errors['phone'] ?></div>
                    <?php endif; ?>
                </div>  
              <div class="col-md-6">
                <label class="form-label">Senha</label>
                <input class="form-control form-control-lg" type="password" name="password">
                <?php if (!empty($errors['password'])): ?><div class="invalid-feedback d-block"><?= $errors['password'] ?></div><?php endif; ?>
              </div>
              <div class="d-grid mt-2"><button class="btn btn-lg btn-primary monster-pulse">Criar conta & começar</button></div>
            </form>
          </div>
          <div class="tab-pane fade <?= $mode === 'magic' ? 'show active' : '' ?>" id="magic">
            <form method="post" class="row g-3 mt-1">
              <?= csrf_input() ?><input type="hidden" name="mode" value="magic">
              <div class="col-12">
                <label class="form-label">E-mail</label>
                <input class="form-control form-control-lg" type="email" name="email">
                <?php if (!empty($errors['email'])): ?><div class="invalid-feedback d-block"><?= $errors['email'] ?></div><?php endif; ?>
              </div>
              <div class="d-grid mt-2"><button class="btn btn-lg btn-secondary">Enviar link mágico</button></div>
              <?php if (!empty($success_magic)): ?>
  <div class="alert alert-success mt-3">
    Link enviado! Verifique seu email.
  </div>
<?php endif; ?>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__.'/footer.php'; ?>
