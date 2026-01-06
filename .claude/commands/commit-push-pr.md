執行完整的 Git 提交到 PR 流程：

1. 執行 `git status` 檢查變更
2. 執行 `git diff` 查看具體變更內容
3. 將相關檔案加入 staging：`git add`
4. 撰寫有意義的 commit message 並提交
5. 推送到遠端：`git push`
6. 如果是新分支，建立 Pull Request：`gh pr create`

注意事項：
- Commit message 使用繁體中文或英文皆可
- PR 標題要簡潔描述變更目的
- PR 描述要包含變更摘要和測試方式
