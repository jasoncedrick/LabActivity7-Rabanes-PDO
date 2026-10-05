# Verification and lab checklist

## Verification status

The source was reviewed against the activity requirements and checked for file references, table constraints, page guards, and owner-scoped update statements. JavaScript syntax was checked with Node. PHP and MySQL were unavailable in the creation environment, so PHP execution, SQL import, browser rendering, and the flows below must still be verified in XAMPP. Do not describe this as a completed runtime test.

## Two-account test

Use a normal window for account A and a private window for account B so their sessions remain separate.

| Test | Expected result |
| --- | --- |
| Import `database.sql` into a fresh MySQL instance/database | `blog_site` contains only `users`, `posts`, and `comments`. |
| Open index.php, post.php, comment.php, or logout.php while logged out | Redirect to login.php. |
| Register valid account A | Redirect to login with a success message. |
| Register A's email again, including uppercase variation | Duplicate email rejected; no duplicate account. |
| Enter blank/space-only name, invalid email, short password, or mismatched confirmation | Validation prevents registration. |
| Enter wrong login credentials | Error; no authenticated session. |
| Log in as A | Feed opens. |
| Visit login.php and register.php while authenticated | Redirect to feed. |
| Create two posts | Newest post appears first; refreshing feed creates no duplicate. |
| Submit blank/space-only or over-limit title/post/comment | Rejected; database unchanged. |
| Register and log in as B in private window | B sees A's posts. |
| B comments on A's post twice | Both comments appear on the correct post with B as author. |
| B manually visits post.php?id=A_POST_ID | Access denied with 404; no edit form. |
| B submits a POST directly to that edit URL | Denied; A's post remains unchanged. |
| A tries comment.php?id=B_COMMENT_ID | Denied, even though A owns the parent post. |
| A edits A's post; B edits B's comment | Changes saved and each Edited badge appears. |
| Save an unchanged post/comment | No new edited timestamp; an existing badge remains. |
| Enter `<script>alert(1)</script>` in a post or comment | Displayed as text; no script executes. |
| Try malformed, negative, or nonexistent IDs | Invalid ID or not-found response; no unauthorized changes. |
| Submit a POST with a missing/incorrect CSRF token | 403; no database write. |
| Bypass browser validation using developer tools or direct HTTP request | PHP still rejects invalid data. |
| Use Log out | Session ends; protected pages redirect to login. |
| Open on a narrow browser window | Navigation wraps and forms remain readable. |

## Cascade test using disposable data only

Create a separate test user, their post, a comment on that post from another test user, and a comment by the first test user on someone else's post. Record their IDs.

In phpMyAdmin, delete the first test user. Confirm their posts are deleted, all comments on those deleted posts are deleted, and their comments on surviving posts are deleted. The other user and their own posts must remain.

Create another disposable post with comments and delete only that post. Its comments should be removed while the users remain. Use only disposable records because these database deletes are permanent after commit.
