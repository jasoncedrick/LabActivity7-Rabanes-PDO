# LabActivity7-Rabanes-PDO

A simple text-only blog using plain PHP, PDO, MySQL, and PHP sessions. No framework, Composer, or build command is needed. The interface uses semantic HTML, readable names, and small shared files.

## 1. Run on Windows inside a VM with XAMPP

1. Inside your Windows virtual machine, open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Download and extract this project inside Windows. Put the folder `LabActivity7-Rabanes-PDO` inside `C:\xampp\htdocs\`. Check that `index.php` is directly at `C:\xampp\htdocs\LabActivity7-Rabanes-PDO\index.php`, not inside a second nested project folder. If you installed XAMPP elsewhere, use that installation's `htdocs` folder.
3. In the **browser inside your Windows VM**, open `http://localhost/phpmyadmin`.
4. Choose **Import**, select this project's `database.sql`, and click **Go**. The SQL creates the database `blog_site` and all three tables. Import once into a fresh database; it intentionally does not drop existing data or overwrite tables.
5. Check `db.php`. The provided local defaults are host `127.0.0.1`, port `3306`, database `blog_site`, username `root`, and a blank password. If your MySQL settings differ, update those values locally. Do not publish actual private credentials.
6. Open `http://localhost/LabActivity7-Rabanes-PDO/`.
7. Register an account, log in, and publish your first post. Create a second account to test ownership and comments.

Keep Apache, MySQL, and the browser inside the Windows VM. There, `localhost` refers to Windows; opening the same URL on your host computer points to the host instead. No VM port forwarding is needed when browsing inside Windows.

Use the localhost URL, not a `file://` URL or VS Code Live Server. PHP must run through Apache. This project expects PHP 8+ with `pdo_mysql` and `mbstring` enabled.

If Apache uses another port, include it in the URL, for example `http://localhost:8080/LabActivity7-Rabanes-PDO/`. The MySQL port in `db.php` is separate from Apache's port.

## 2. Files and responsibilities

| File | Responsibility |
| --- | --- |
| `database.sql` | Creates `blog_site`, its three tables, and foreign keys. |
| `db.php` | Holds database configuration and creates the `$pdo` connection. |
| `includes/functions.php` | Starts the session; provides authentication guards, escaping, validation, and CSRF helpers. |
| `register.php` | Guest-only registration; validates input and hashes passwords. |
| `login.php` | Guest-only authentication; verifies the hash and stores the user ID in the session. |
| `logout.php` | Checks a POST form token, clears the session, and redirects to login. |
| `index.php` | Authenticated news feed showing everyone's posts and comments. |
| `post.php` | Creates a post or edits one owned by the logged-in user. |
| `comment.php` | Creates a comment or edits one owned by the logged-in user. |
| `includes/header.php` / `footer.php` | Shared semantic page layout. |
| `includes/errors.php` | Displays validation errors. |
| `assets/style.css` | Simple responsive styling. |
| `assets/validation.js` | Extra browser checks for whitespace and matching passwords. |
| `EXPLANATION.md` | Beginner-friendly walkthrough for explaining the code. |
| `TESTING.md` | Two-account functional and security test checklist. |

## 3. Requirement mapping

| Activity requirement | Implementation |
| --- | --- |
| Database named `blog_site` | `CREATE DATABASE` and `USE` in `database.sql`. |
| Only users, posts, comments | Exactly three `CREATE TABLE` statements. Sessions use PHP's session storage, not an extra table. |
| Comments as a junction table | `comments` has `post_id` and `user_id` foreign keys, plus comment text and timestamps. |
| Auto-increment primary keys | Each table uses `id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY`. |
| Unique email | `users.email` has `UNIQUE`; duplicate registration is handled. |
| Cascade deletion | All three foreign keys have `ON DELETE CASCADE`. |
| Client and server validation | HTML constraints plus JavaScript; PHP repeats validation before SQL writes. |
| Separate PDO file, imported using require | Every database page uses `require __DIR__ . '/db.php'`. |
| Native password functions | Registration uses `password_hash`; login uses `password_verify`. |
| Guest-only onboarding | `requireGuest()` guards login and registration. |
| Protected other pages | `requireLogin()` guards feed, post, comment, and logout endpoints before database work. |
| Most recent posts first | Feed sorts by `created_at DESC, id DESC`. Editing does not change the posting date. |
| Text posts and comments | Forms accept text; output is escaped and line breaks are preserved with CSS. |
| Edit only own posts/comments | Owner-scoped SELECT and UPDATE statements use the session user ID. |
| Edited marker | `edited_at` starts NULL, changes on actual edits, and displays an `Edited` badge. |

The activity does not require delete buttons, so the interface includes creation and editing only. Database cascades still work when parent rows are deleted directly in SQL.

## 4. Publish the public repository

Create an empty **public** repository named `LabActivity7-Rabanes-PDO` on your GitHub account. Do not initialize it with another README if using these commands.

With Git for Windows installed, open **PowerShell inside the Windows VM**, change into the project folder, and run:

```powershell
cd C:\xampp\htdocs\LabActivity7-Rabanes-PDO
git init
git add .
git commit -m "Complete PDO blog activity"
git branch -M main
git remote add origin https://github.com/jasoncedrick/LabActivity7-Rabanes-PDO.git
git push -u origin main
```

Authenticate with your GitHub setup when prompted. Do not put a password or token inside source files or the remote URL. Alternatively, upload the project contents through GitHub's web interface and commit them. Ensure `index.php` and `README.md` are at the repository root.

GitHub stores the code; GitHub Pages does not run this PHP/MySQL application. Run it in XAMPP for your demonstration.

## 5. Submission

Run through `TESTING.md`, understand `EXPLANATION.md`, and submit the public repository URL. The instructor also requires being present in the lab while conducting the activity. These files do not satisfy attendance on your behalf.
