# 微信小程序接入 - 实现说明

## 项目结构

```
wechat/
├── TODO.txt                    # 任务清单
├── README.md                   # 本文件
└── miniapp/                    # 小程序项目
    ├── app.json               # 小程序配置
    ├── app.js                 # 小程序入口
    ├── app.wxss               # 全局样式
    ├── project.config.json    # 项目配置
    ├── sitemap.json           # 站点地图
    ├── pages/                 # 页面目录
    │   ├── index/             # 首页
    │   ├── scan/              # 扫码页面
    │   ├── doctor-register/   # 医生注册页面
    │   └── patient-bind/      # 患者绑定页面
    └── utils/                 # 工具类
        ├── api.js             # API调用封装
        ├── wechat.js          # 微信API封装
        └── storage.js         # 本地存储封装

custom/wechat_miniapp/         # 后端代码
├── migrations/                # 数据库迁移
│   └── add_wechat_fields.sql
├── Services/                  # 服务层
│   ├── WeChatQrCodeService.php
│   ├── WeChatUserService.php
│   └── WeChatPatientService.php
├── Controllers/               # 控制器
│   └── WeChatMiniappController.php
├── Routes/                    # 路由定义
│   └── wechat_routes.php
└── Listeners/                 # 事件监听器
    └── WeChatRouteListener.php
```

## 部署步骤

### 1. 执行数据库迁移

**⚠️ 重要：执行迁移前请先备份数据库！**

```bash
# 方法一：使用备份脚本（推荐）
bash custom/wechat_miniapp/migrations/backup_before_migration.sh

# 方法二：手动备份
mysqldump -u root -p openemr > openemr_backup_$(date +%Y%m%d_%H%M%S).sql

# 执行迁移
mysql -u root -p openemr < custom/wechat_miniapp/migrations/add_wechat_fields.sql

# 验证迁移状态（可选）
mysql -u root -p openemr < custom/wechat_miniapp/migrations/check_migration_status.sql
```

**如果需要回滚迁移**：
```bash
# 使用安全回滚脚本（推荐）
mysql -u root -p openemr < custom/wechat_miniapp/migrations/rollback_wechat_fields_safe.sql

# 或从备份恢复
mysql -u root -p openemr < openemr_backup_YYYYMMDD_HHMMSS.sql
```

详细的迁移说明请查看：`custom/wechat_miniapp/migrations/README.md`

### 2. 配置小程序

编辑 `wechat/miniapp/app.js`，修改 `baseUrl`：

```javascript
globalData: {
  baseUrl: 'https://your-openemr-host/apis/default/api',
  // ...
}
```

编辑 `wechat/miniapp/project.config.json`，设置你的小程序 AppID：

```json
{
  "appid": "your-miniapp-appid",
  // ...
}
```

### 3. 配置后端

路由已自动集成到 `StandardRouteFinder.php`，无需额外配置。

### 4. 测试 API

```bash
# 测试生成医生注册二维码
curl -X GET "https://your-host/apis/default/api/wechat/qrcode/doctor"

# 测试生成患者绑定二维码
curl -X GET "https://your-host/apis/default/api/wechat/qrcode/patient?doctor_id=1"
```

## 使用流程

### 医生注册流程

1. 管理员调用 API 生成二维码：`GET /api/wechat/qrcode/doctor`
2. 医生在小程序中扫码
3. 小程序获取微信 OpenID
4. 调用绑定接口：`POST /api/wechat/bind`
5. 填写注册信息并提交：`POST /api/wechat/doctor/register`

### 患者绑定流程

1. 医生调用 API 生成二维码：`GET /api/wechat/qrcode/patient?doctor_id=123`
2. 患者在小程序中扫码
3. 小程序获取微信 OpenID
4. 调用绑定接口：`POST /api/wechat/bind`
5. 填写患者信息并提交：`POST /api/wechat/patient/register`

## API 端点

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/api/wechat/qrcode/doctor` | 生成医生注册二维码 |
| GET | `/api/wechat/qrcode/patient?doctor_id=123` | 生成患者绑定二维码 |
| POST | `/api/wechat/bind` | 绑定微信OpenID到二维码 |
| POST | `/api/wechat/doctor/register` | 医生注册 |
| POST | `/api/wechat/patient/register` | 患者注册 |
| GET | `/api/wechat/qrcode/status?code=WX...` | 检查二维码状态 |

## 注意事项

1. **微信 OpenID 获取**：小程序需要通过 `wx.login()` 获取 code，然后调用后端接口换取 openid。需要在后端实现一个接口来处理这个逻辑。

2. **路由集成**：路由已集成到 `StandardRouteFinder.php`，这是最小侵入的方式。如果需要移除，只需删除相关代码即可。

3. **数据库字段**：所有微信相关字段都添加了索引，确保查询性能。

4. **安全性**：
   - 二维码有过期时间（默认10分钟）
   - OpenID 验证确保绑定安全
   - 所有输入数据需要验证

5. **小程序配置**：
   - 需要在微信公众平台配置服务器域名
   - 需要在 `project.config.json` 中设置正确的 AppID

## 待完成事项

1. **后端接口**：实现微信 code 换取 openid 的接口（需要配置微信 AppID 和 AppSecret）
2. **ACL 权限设置**：在 `WeChatUserService.php` 中实现 `setDefaultDoctorAcl` 方法
3. **错误处理**：完善错误处理和日志记录
4. **测试**：完整的功能测试和集成测试

## 技术支持

如有问题，请查看：
- `wechat/TODO.txt` - 任务清单
- `custom/wechat_miniapp/` - 后端代码
- `wechat/miniapp/` - 小程序代码

---

最后更新：2024年
