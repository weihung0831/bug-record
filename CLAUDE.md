# Bug Record 專案指南

## 專案概述
Laravel 12 全端應用，使用 Vite + Tailwind CSS 4.0

## 技術棧
- PHP 8.2+ / Laravel 12
- Vite 7.0 / Tailwind CSS 4.0
- Pest 測試框架

## 開發指令
```bash
composer dev      # 啟動開發環境（伺服器 + 隊列 + Vite）
composer test     # 執行測試
npm run build     # 生產環境構建
```

## 專案結構
- `app/Http/Controllers/` - 控制器
- `app/Models/` - Eloquent 模型
- `resources/views/` - Blade 模板
- `resources/js/` - 前端 JS
- `database/migrations/` - 資料庫遷移

## 編碼規範
- 遵循 PSR-12 PHP 編碼標準
- 控制器使用單一職責原則
- 模型關聯使用 Eager Loading 避免 N+1
- 前端樣式優先使用 Tailwind utility classes

## Claude Code 開發流程（Boris Cherny 13 Tips）

### 1. 並行多個終端實例
同時開 3-5 個終端視窗運行 Claude Code，用不同 git checkout 處理不同任務。
開啟系統通知，知道哪個實例需要輸入。

### 2. Web + Mobile 搭配使用
除了終端，也可在 claude.ai/code 開啟 Web 版本。
手機上用 Claude iOS App 啟動任務，之後在電腦上繼續。

### 3. 使用 Opus 4.5 + Thinking
優先使用 Opus 4.5 + 思考模式。雖然較慢，但減少修正次數，總成本更低。

### 4. 共享 CLAUDE.md
團隊共用此文件，提交到 git。發現 Claude 做錯的事就記錄下來，避免下次再犯。

### 5. PR 上使用 @.claude
Code review 時在 PR 留言 tag @.claude，讓它協助修改或更新 CLAUDE.md。
需先安裝 GitHub Action：`/install-github-action`

### 6. Plan Mode 開始
大部分工作從 Plan Mode 開始（shift+tab 兩次）。
先跟 Claude 來回討論計畫，滿意後再切換到自動接受模式，通常能一次完成。

### 7. 使用 Slash Commands
將每天重複的工作流封裝成命令，存在 `.claude/commands/`：
- `/test` - 執行測試並分析
- `/review` - 程式碼審查
- `/migrate` - 資料庫遷移
- `/feature [功能名稱]` - 規劃新功能
- `/translate [內容或檔案路徑]` - 翻譯成繁體中文

### 8. 使用 Subagents
設定子代理自動化常見工作流：
- `code-simplifier` - 完成後簡化程式碼
- `verify-app` - 端到端測試驗證

### 9. 使用 Hooks
設定 PostToolUse hooks 自動執行：
- 程式碼格式化（prettier、pint）
- 型別檢查
- Lint 檢查

### 10. 使用 /permissions 而非跳過
不要用 `--dangerously-skip-permissions`。
改用 `/permissions` 或 `.claude/settings.json` 預先允許信任的工具。

### 11. MCP 連接外部工具
透過 MCP 連接 Slack、資料庫、Sentry 等，設定存在 `.mcp.json`。
讓 Claude 從程式碼編輯器變成完整工作流中心。

### 12. 長任務設定
耗時任務使用背景代理（background agents）或 Stop hooks。
在沙箱中用寬鬆權限模式，讓任務不被提示阻擋。

### 13. 反饋循環驗證（最重要）
讓 Claude 有方法驗證自己的工作，品質可提升 2-3 倍：
```
寫完後執行 composer test 驗證，失敗就自動修復直到通過
```

## Prompt 範例

### 新功能開發（先規劃）
```
幫我實作 [功能描述]，先規劃不要直接寫 code
```

### 修 Bug（反饋循環）
```
修復這個 bug：[描述問題]
修完後跑 composer test 驗證，失敗就自動修到過
```

### 完整開發流程
```
我要新增 [功能描述]
1. 先規劃實作步驟
2. 寫完後跑測試驗證
3. 測試過了就 /commit-push-pr
```

### MCP 查資料庫
```
查一下資料庫有哪些 tables
查詢最近 10 筆 [table] 資料
```

### MCP 查 Sentry 錯誤
```
看一下 Sentry 最近有什麼錯誤
分析 Sentry 上的這個錯誤並修復它
```

### MCP 操作 GitHub
```
幫我建一個 issue：標題是「[標題]」
看一下目前有哪些 open 的 PR
```

### Slash Commands
- `/test` - 執行測試
- `/review` - 審查程式碼
- `/commit-push-pr` - 提交並建立 PR
- `/fix [問題]` - 修復指定問題
- `/feature [功能]` - 規劃新功能
- `/lint` - 程式碼品質檢查
- `/simplify` - 簡化重構程式碼
- `/migrate` - 資料庫遷移
- `/translate [內容或檔案路徑]` - 翻譯成繁體中文

## 常見問題與解決方案

### 1. Vite 與 Vue 套件版本衝突
**問題**：執行 `npm install` 時出現套件相依性衝突錯誤
**解法**：把 vite 降級到 ^6.0.0，或用 `npm install --force` 強制安裝

### 2. Tailwind CSS 4 的 PostCSS 設定問題
**問題**：出現 PostCSS autoprefixer 找不到的錯誤
**解法**：使用 `@tailwindcss/vite` 外掛時，把 `postcss.config.js` 裡的 plugins 清空

### 3. 單元測試出現「找不到 Facade 根目錄」錯誤
**問題**：單元測試無法使用 Laravel 的 Facade 功能
**解法**：在 `tests/Pest.php` 的 RefreshDatabase 設定加入 `'Unit'`：
```php
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');
```

### 4. 控制器缺少授權驗證方法
**問題**：呼叫 `$this->authorize()` 時出現方法不存在的錯誤
**解法**：在 `app/Http/Controllers/Controller.php` 加入授權驗證功能：
```php
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;
}
```

### 5. 模型欄位預設值設定
**問題**：新增資料時某些欄位變成空值
**解法**：在模型中用 `$attributes` 設定預設值：
```php
protected $attributes = [
    'status' => 'open',
    'priority' => 'medium',
];
```

### 6. 首頁路由重導向邏輯
**問題**：已登入的使用者訪問首頁 `/` 還是被導向登入頁
**解法**：根據登入狀態做不同的重導向：
```php
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
```

## 待辦事項
<!-- 記錄未完成的功能或改進 -->
