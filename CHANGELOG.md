# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- 新增 API 登入功能，使用 Laravel Sanctum 進行代幣驗證。
- 新增 `AuthController` 處理 `login` 與 `logout` 請求。
- 在 `User` 模型中啟用 `HasApiTokens` 以支援 API 認證。
- 更新 `routes/api.php` 加入認證相關路由。
