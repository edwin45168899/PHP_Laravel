# PHP Laravel 學習專案

本專案提供一個基於 Docker 的完整 Laravel 開發環境，包含 Nginx、PHP-FPM (PHP 8.3) 以及預載的 Xdebug 偵錯工具。

---

## 環境
Windows 11 + WSL2 + Docker Desktop

## 專案結構

- `docker-compose.yml`: 環境定義檔
- `docker/php/Dockerfile`: PHP 映像檔定義（含 Composer, Xdebug）
- `nginx/default.conf`: Nginx 設定檔
- `php-conf/xdebug.ini`: Xdebug 設定
- `src/`: Laravel 原始碼目錄
- **MySQL**: 8.0 資料庫
- **Adminer**: 網頁版資料庫管理工具 (Port 8081)

---

## 快速啟動

### 1. 啟動模式切換

你可以根據練習需要決定是否啟動 MySQL（以節省電腦資源）：

- **輕量模式 (不帶 MySQL)**：
  ```bash
  docker compose up -d
  ```

- **完整模式 (啟動 MySQL + Adminer)**：
  ```bash
  docker compose --profile mysql up -d
  ```

### 2. 重啟或進入環境
- **進入介面**：[http://localhost:8080](http://localhost:8080)
- **停止所有服務**：
  ```bash
  # 如果你有啟動 mysql profile，必須帶上 profile 才能完全關閉
  docker compose --profile mysql down
  ```

> ⚠️ **注意：為什麼只打 `docker compose down` 會殘留 MySQL？**
> 因為 MySQL 目錄被歸類在 `mysql` profile 中。如果你在啟動時使用了 profile，但關閉時沒加，Docker 會認定你「只想關閉預設服務 (PHP/Nginx)」，從而導致 MySQL 容器繼續在背景執行。

3. **使用 Artisan 與 Tinker**：
   ```bash
   # 進入 Tinker 練習語法
   docker exec -it php-learn php artisan tinker

   # 執行 Artisan 指令
   docker exec -it php-learn php artisan [command]
   ```

---

## 🗄️ 資料庫管理與切換

本環境支援 **MySQL** 與 **SQLite** 兩種模式，你可以隨時切換。

### 1. 切換資料庫 (在 `src/.env` 修改)

要切換資料庫，請編輯 `src/.env` 檔案中的 `DB_CONNECTION` 區段：

#### 🔹 模式 A: MySQL (建議，模擬公司環境)
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

#### 🔹 模式 B: SQLite (輕量，不需啟動 MySQL 容器)
```env
DB_CONNECTION=sqlite
# 下面四行在 SQLite 模式下可省略或註解掉
# DB_HOST=db
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=laravel
# DB_PASSWORD=secret
```

---

### 2. 重置資料庫與重建資料 (重要)

當你修改了 `.env` 設定、或想要清空所有資料並重新產生測試資料時，請執行：

```bash
# 此指令會刪除所有資料表 -> 重新建立 -> 跑初始 Seed (含 10 筆測試 User)
docker exec -it php-learn php artisan migrate:fresh --seed
```

---

## 🛠️ Adminer (資料庫管理工具)

不需安裝額外軟體，直接透過瀏覽器管理資料庫：
- **存取網址**：[http://localhost:8081](http://localhost:8081)
- **登入資訊**：
  - 系　統：`MySQL`
  - 伺服器：`db`
  - 使用者：`laravel`
  - 密　碼：`secret`
  - 資料庫：`laravel`

---

## 🏗️ Laravel 資料庫開發流程 (Workflow)

在 Laravel 中，我們不建議直接在 MySQL 裡寫 SQL 建立資料表，而是使用 **Migration (遷移)** 機制。

### 步驟 1：建立遷移檔
在終端機執行，這會產生一個新的檔案在 `src/database/migrations/` 下：
```bash
docker exec -it php-learn php artisan make:migration create_posts_table
```

### 步驟 2：定義欄位
打開剛產生的檔案，在 `up()` 方法中定義欄位：
```php
public function up(): void {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // 建立一個字串欄位
        $table->text('content');  // 建立一個長文字欄位
        $table->timestamps();
    });
}
```

### 步驟 3：執行遷移
將定義好的內容同步到 MySQL 中：
```bash
docker exec -it php-learn php artisan migrate
```

### 步驟 4：建立模型 (Model)
為了能用 PHP 操作這個表，建議建立對應的 Model：
```bash
docker exec -it php-learn php artisan make:model Post
```
之後你就可以在程式碼中使用 `Post::all()` 來讀取資料了。

---

## 🚀 偵錯方法 (Xdebug 詳解)

本環境已經針對 Laravel 優化了偵錯設定，支援中斷點 (Breakpoint) 與變數監看。

### 1. 啟動監聽 (VS Code)
- 切換到 VS Code 的「執行與偵錯」(Ctrl+Shift+D)。
- 下拉選單選擇 **"Listen for Xdebug (Docker)"**。
- 按下綠色播放鍵（或 `F5`），底部狀態列變為橘色/藍色即代表監聽中。
- .vscode/launch.json
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug (Docker)",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}/src"
            },
            "log": false
        }
    ]
}
```

### 2. 設定進入點 (以 Route 為例)
- 打開 `src/routes/web.php`。
- 在第 6 行 `return view('welcome');` 左側點擊一下，出現 **紅點**。

### 3. 觸發偵錯
- 打開瀏覽器存取 [http://localhost:8080](http://localhost:8080)。
- 瀏覽器會進入加載狀態，此時 VS Code 會自動跳出並黃色高亮該行，你可以在左側看到當前的全域變數與物件狀態。

### 📌 常見 Q&A
- **斷不住？** 請檢查 `docker-compose.yml` 中的 `extra_hosts` 是否有 `host.docker.internal:host-gateway` (WSL2 必要)。
- **路徑不正確？** 偵錯設定已鎖定 `pathMappings` 為 `/var/www/html` 映射到本地的 `${workspaceFolder}/src`。

---

## 🤖 GitHub Actions 學習歷程

本專案包含一系列 GitHub Actions 練習，用於學習自動化工作流 (CI/CD)。

### 練習 1：Hello World (基礎語法與觸發)
*   **檔案路徑**：`.github/workflows/hello-world.yml`
*   **學習重點**：
    *   `on: [push]`：設定觸發條件。
    *   `jobs` 與 `steps`：定義工作流結構。
    *   `run`：執行 Shell 指令。
    *   `${{ github.actor }}`：使用 GitHub Actions 內建變數。
*   **驗證方式**：推送代碼至 GitHub 後，在 Repo 的 **Actions** 標籤頁查看執行結果。

### 練習 2：Laravel Pint (自動化代碼排版檢查)
*   **檔案路徑**：`.github/workflows/laravel-pint.yml`
*   **學習重點**：
    *   `uses: actions/checkout@v4`：學習如何引用現成的 Action 插件。
    *   `shivammathur/setup-php@v2`：設定特定的 PHP 版本。
    *   `working-directory`：當 Laravel 在子目錄 (如 `src/`) 時，如何指定執行路徑。
    *   **CI 概念**：讓機器幫你檢查代碼規範，不符規範的工作流會變為紅色（失敗）。
*   **驗證方式**：推送後查看 Actions，試著故意寫出排版醜陋的 PHP 代碼（如亂點空格），看看 Action 是否會報錯。

---

## 學習建議

- **路由練習**：修改 `src/routes/web.php` 練習定義 API 與網頁。
- **語法練習**：頻繁使用 `php artisan tinker` 驗證小段程式碼。
- **資料庫**：目前已安裝 `pdo_mysql` 擴充，如需資料庫容器可進一步擴充此環境。

---

## 🌐 API 練習範例

本專案已建立一個簡單的 API 範例，示範如何從 MySQL 讀取資料並以 JSON 回傳。

### 1. 範例路徑
- **URL**: [http://localhost:8080/api/users](http://localhost:8080/api/users)
- **控制器**: `src/app/Http/Controllers/Api/UserController.php`
- **路由定義**: `src/routes/api.php`

### 2. 測試方式
- **瀏覽器**: 直接開啟上述連結。
- **VS Code REST Client**:
  1. 開啟專案根目錄下的 `local.http`。
  2. 點擊 `GET http://localhost:8080/api/users` 上方的 **"Send Request"** 字樣。
  *這是在開發 API 時最推薦的測試方式。*

### 3. 如何增加測試資料
如果你想增加更多隨機使用者資料，可以執行：
```bash
docker exec -it php-learn php artisan tinker --execute="App\Models\User::factory()->count(5)->create()"
```

---

## 常用指令備忘錄

- 重啟服務：`docker compose restart`
- 查看日誌：`docker compose logs -f`
- 重新建立環境：`docker compose up -d --build --force-recreate`
