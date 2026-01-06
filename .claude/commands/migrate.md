執行資料庫遷移流程：

1. 檢查是否有新的遷移檔案
2. 執行 `php artisan migrate --pretend` 預覽變更
3. 確認無誤後執行 `php artisan migrate`
4. 驗證遷移是否成功
