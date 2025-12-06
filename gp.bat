@echo off
setlocal
set "commit_message=%~1"
if not defined commit_message (
    set "commit_message=Changes"
)
echo "Adding all files..."
git add .
echo "Committing with message: '%commit_message%'"
git commit -m "%commit_message%"
echo "Pushing to remote..."
git push
endlocal