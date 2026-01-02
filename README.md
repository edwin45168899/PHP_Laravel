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

1. **啟動容器**：
   在根目錄執行：
   ```bash
   docker compose up -d --build
   ```

2. **進入 Laravel 環境**：
   存取：[http://localhost:8080](http://localhost:8080)

3. **使用 Artisan 與 Tinker**：
   ```bash
   # 進入 Tinker 練習語法
   docker exec -it php-learn php artisan tinker

   # 執行 Artisan 指令
   docker exec -it php-learn php artisan [command]
   ```

---

## �️ 資料庫設定 (MySQL)

本環境已內建 MySQL 8.0。

1. **Laravel .env 設定**：
   確保 `src/.env` 如下配置：
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=laravel
   DB_PASSWORD=secret
   ```

2. **執行資料表遷移 (Migration)**：
   ```bash
   docker exec -it php-learn php artisan migrate
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

## �🚀 偵錯方法 (Xdebug 詳解)

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

## 學習建議

- **路由練習**：修改 `src/routes/web.php` 練習定義 API 與網頁。
- **語法練習**：頻繁使用 `php artisan tinker` 驗證小段程式碼。
- **資料庫**：目前已安裝 `pdo_mysql` 擴充，如需資料庫容器可進一步擴充此環境。

---

## 常用指令備忘錄

- 重啟服務：`docker compose restart`
- 查看日誌：`docker compose logs -f`
- 重新建立環境：`docker compose up -d --build --force-recreate`
