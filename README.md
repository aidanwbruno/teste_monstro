# TESTE – O MONSTRO QUE TE HABITA (PHP + MySQL)

Aplicação completa com **16 perguntas**, **textos completos** de feedback (A..H), **Argon2id**, **Magic Link**, **Bootstrap 5** (tema escuro).

> **Créditos do conteúdo**  
> Todo o **conteúdo textual do teste** (perguntas, títulos, resumos e textos de resultados A..H) foi **desenvolvido pelo autor _Wilsius Norte_**.  
> Este repositório implementa apenas a aplicação (código e interface) que exibe/operacionaliza esse conteúdo.

## Stack
- PHP 8.1+ (PDO)
- MySQL 8+
- Argon2id (`password_hash` nativo)
- Magic link (token com validade) — envio por SMTP ou `mail()` (com HTML + texto alternativo)
- Bootstrap 5.3 (CDN), CSS escuro customizado
- CSRF + prepared statements

## Rodando
1. Crie o banco e tabelas com `app/includes/schema.sql`.
2. Copie `app/includes/config.example.php` para `app/includes/config.php` e ajuste as credenciais.
3. Suba o servidor:
   ```bash
   php -S localhost:8080 -t .
   ```
4. Acesse `http://localhost:8080/`.

## Fluxo
- **Registro**: nome, e-mail, telefone e **senha (Argon2id)**.
- **Login**: e-mail + senha **ou** **link mágico** enviado ao e-mail (em `debug=true`, o link pode ser exibido na tela).
- **Teste**: 16 perguntas A..H, cálculo do dominante, persistência de JSON e letra dominante (sobrescreve tentativa anterior).
- **Resultado**: exibe título, resumo e texto completos de acordo com a letra dominante; botões de ação (refazer, início, abrir site, compartilhar).

## Pastas
```
/app
  /includes (config, db, csrf, helpers, questions, results, schema.sql)
  /pages (home/login, test, result, magic)
/public (css/js)
index.php
```

## Produção (dicas rápidas)
- Ative HTTPS e `Secure` nos cookies (ajuste no servidor).
- Configure **SMTP** real no `includes/config.php` (o código usa PHPMailer; há fallback para `mail()`).
- Adicione rate-limit (Nginx/Apache) e logging.
- Defina variáveis de ambiente/segredos fora do repositório.

## Contato do autor (Wilsius Norte)
> Para usos editoriais, licenciamento do conteúdo, parcerias e convites.

- **Site:** [wnortepsi.com](https://wnortepsi.com) <!-- substitua pela URL correta -->
- **Instagram:** [@wnortepsi](https://www.instagram.com/wnortepsi/) <!-- substitua -->

## Licença de código & direitos autorais do conteúdo
- **Conteúdo textual do teste**: © **Wilsius Norte**. O uso, reprodução e/ou distribuição do **conteúdo do teste** (perguntas, títulos, resumos e textos de resultados) dependem de autorização do autor.
