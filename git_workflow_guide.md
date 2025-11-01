# Git Workflow Guide

Understanding the basic Git workflow is crucial for effective version control. This guide explains the core concepts and when to use key commands like `git add`, `git commit`, `git push`, and `git pull`.

## The Four States of Git

Git tracks your files through four main states:

1.  **Working Directory (or Working Tree):** This is the directory on your file system where you are currently making changes to your project files. When you edit a file, it's in the working directory.

2.  **Staging Area (or Index):** This is an intermediate area where you prepare changes before committing them. You add changes from your working directory to the staging area to tell Git, "These are the specific changes I want to include in my next commit."

3.  **Local Repository:** This is where Git permanently stores your project's history on your local machine. When you `commit` changes, they are saved here as a new snapshot.

4.  **Remote Repository:** This is a version of your project hosted on a server (e.g., GitHub, GitLab, Bitbucket). It serves as a central point for collaboration and backup. Your local repository can synchronize with one or more remote repositories.

## Key Git Commands and When to Use Them

### `git add`

**What it does:** Moves changes from your **Working Directory** to the **Staging Area**.

**When to use it:**
*   After you've made modifications to one or more files and you want to include those specific changes in your next commit.
*   You can add individual files (`git add <file_name>`), specific changes within a file (`git add -p`), or all changes in the current directory (`git add .`).
*   **Best Practice:** Use `git add` to carefully select which changes belong together in a logical commit. Avoid `git add .` blindly if you have unrelated changes.

### `git commit`

**What it does:** Takes the changes currently in the **Staging Area** and permanently saves them as a new snapshot (a commit) in your **Local Repository**.

**When to use it:**
*   After you've staged all the related changes for a specific task, bug fix, or feature.
*   Each commit should represent a single, logical unit of work.
*   **Best Practice:** Always write clear, concise, and descriptive commit messages. A good commit message explains *why* the change was made, not just *what* was changed.
    ```bash
    git commit -m "Fix: Resolve issue with user authentication on login page"
    ```

### `git pull`

**What it does:** Fetches changes from a **Remote Repository** and integrates them into your current branch in your **Local Repository**.

**When to use it:**
*   **Before you start working:** Always `git pull` at the beginning of your workday or before starting a new task to ensure your local branch is up-to-date with the remote. This helps prevent merge conflicts.
*   **Before pushing:** It's good practice to `git pull` before `git push` to incorporate any new changes from others, which can help you resolve conflicts locally before pushing.
*   **Best Practice:** Use `git pull --rebase` if you want to keep a linear history and avoid unnecessary merge commits, especially on feature branches. However, be cautious with `rebase` on shared branches.

### `git push`

**What it does:** Uploads your committed changes from your **Local Repository** to a **Remote Repository**.

**When to use it:**
*   After you have committed your changes locally and you want to share them with others or back them up on the remote server.
*   **Best Practice:** Only `git push` changes that are complete, tested, and ready to be shared. Always `git pull` before pushing to minimize conflicts.

## Typical Workflow Summary

1.  **Start fresh:** `git pull origin <branch_name>` (or `git pull --rebase origin <branch_name>`) to get the latest changes.
2.  **Make changes:** Edit files in your **Working Directory**.
3.  **Stage changes:** `git add <file_name>` for specific changes, or `git add .` for all changes.
4.  **Commit changes:** `git commit -m "Your descriptive message"` to save them locally.
5.  **Repeat 2-4** as you continue working on your task.
6.  **Before sharing:** `git pull origin <branch_name>` again to ensure you have the absolute latest remote changes and resolve any conflicts locally.
7.  **Share your work:** `git push origin <branch_name>` to upload your local commits to the remote repository.

By following these steps, you can maintain a clean, organized, and collaborative Git history.