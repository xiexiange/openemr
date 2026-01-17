# OpenEMR 医生与患者视频通话使用指南

## 功能概述

OpenEMR 通过 **Comlink Telehealth** 模块支持医生与患者之间的视频通话功能。这是一个**付费的第三方服务**（约 $16/月/医生，无限会话）。

---

## 前置条件

### 1. 安装和配置 Comlink Telehealth 模块

**注意**：这是付费服务，需要：
- 购买 Comlink Telehealth 服务
- 配置 API 凭据
- 完成医生和患者的注册

**配置步骤**：
1. 访问 OpenEMR Wiki: [Comlink Telehealth Technical Guide](https://www.open-emr.org/wiki/index.php/Comlink_Telehealth)
2. 按照指南配置模块
3. 设置 API 凭据和全局配置

### 2. 确保模块已启用

- 检查模块是否已安装：`Admin → Modules → Manage Modules`
- 确认 Comlink Telehealth 模块已激活

### 3. 医生和患者注册

- 医生需要在系统中注册 Telehealth 账号
- 患者会自动注册（auto-provision）
- 确保预约已关联正确的医生和患者

---

## 使用方式

### 方式一：从日历（Calendar）发起视频通话（推荐）

**医生端操作**：

1. **访问日历**
   ```
   Calendar → 选择预约 → 点击预约查看详情
   ```

2. **查看预约详情**
   - 在预约详情页面，找到 **"Launch TeleHealth Session"**（启动远程医疗会话）按钮
   - 按钮图标：🎥 视频图标

3. **启动视频会话**
   - 点击 **"Launch TeleHealth Session"** 按钮
   - 系统会自动：
     - 创建或关联就诊记录（Encounter）
     - 打开视频会议室界面
     - 等待患者加入

4. **视频会话时间限制**
   - 只能在预约时间的**前后2小时内**启动会话
   - 如果超出时间范围，按钮会显示 "TeleHealth Session Expired"（会话已过期）

5. **会话状态**
   - **Active（活跃）**：显示蓝色按钮，可以点击启动
   - **Completed（已完成）**：会话已结束，按钮禁用
   - **Unenrolled（未注册）**：医生或患者未注册，按钮禁用

**患者端操作**：

1. **登录患者门户**
   ```
   访问患者门户 → 登录账号
   ```

2. **查看预约**
   - 在预约列表中，找到对应的预约
   - 预约卡片会显示视频图标按钮

3. **加入视频会话**
   - 点击预约卡片上的视频按钮
   - 等待医生发起连接
   - 允许浏览器访问摄像头和麦克风

### 方式二：从预约编辑页面发起

1. **打开预约**
   ```
   Calendar → 点击预约 → 编辑预约详情
   ```

2. **启动会话**
   - 在预约编辑/详情页面，会显示 **"Launch TeleHealth Session"** 按钮
   - 点击按钮启动视频通话

### 方式三：从就诊记录（Encounter）发起

如果预约已经关联了就诊记录：

1. **打开患者文件**
   ```
   Patient → 选择患者 → Visits → Current
   ```

2. **查找视频通话入口**
   - 在就诊记录页面，查找 Telehealth 相关按钮或链接
   - 或者从预约详情页面启动（推荐）

---

## 功能限制和注意事项

### 时间限制
- ✅ 只能在预约时间**前后2小时**内启动视频会话
- ❌ 超出时间范围无法启动

### 状态限制
- ✅ 预约状态必须为"活跃"（Active）
- ❌ 如果预约已完成（Completed/Checked Out），无法启动新会话

### 权限要求
- 医生需要 `patients -> appt -> write` 或 `patients -> appt -> wsome` 权限
- 需要预约相关的 ACL 权限

### 技术要求
- **浏览器支持**：需要支持 WebRTC 的现代浏览器（Chrome、Firefox、Safari、Edge）
- **摄像头和麦克风**：需要允许浏览器访问
- **网络连接**：需要稳定的网络连接
- **HTTPS**：建议使用 HTTPS 连接以确保安全

---

## 视频会话功能

### 会话控制

视频会话界面通常包括：

1. **视频窗口**
   - 本地视频（自己的画面）
   - 远程视频（对方画面）
   - 画中画模式

2. **控制按钮**
   - 🎥 **摄像头开关**：开启/关闭视频
   - 🎤 **麦克风开关**：开启/关闭音频
   - 📺 **屏幕共享**：共享屏幕（如果支持）
   - 📞 **挂断**：结束通话

3. **等待室（Waiting Room）**
   - 患者可以先进入等待室
   - 医生进入后，患者会被接入会话

4. **会话记录**
   - 会话会自动关联到就诊记录
   - 可以在患者文件中查看会话历史

---

## 配置说明

### 全局配置

在 `Admin → Globals` 中配置以下设置：

- `comlink_telehealth_video_uri` - 视频服务 API URI
- `comlink_telehealth_registration_uri` - 注册服务 API URI
- `comlink_telehealth_user_id` - API 用户 ID
- `comlink_telehealth_user_password` - API 密码
- `comlink_telehealth_cms_id` - CMS ID
- `comlink_autoprovision_provider` - 自动注册医生
- `comlink_telehealth_thirdparty_enabled` - 启用第三方邀请

### 预约类别配置

确保预约类别（Appointment Category）已启用 Telehealth 支持：
- 在 `Admin → Clinic → Calendar` 中配置预约类别
- 某些类别可能需要特殊配置

---

## 常见问题

### Q1: 为什么看不到 "Launch TeleHealth Session" 按钮？

**可能原因**：
1. Comlink Telehealth 模块未安装或未启用
2. 医生未注册 Telehealth 服务
3. 预约类别不支持 Telehealth
4. 预约已过期或已完成
5. 不在时间窗口内（前后2小时）

**解决方法**：
- 检查模块状态
- 确认医生已注册
- 检查预约时间和状态

### Q2: 患者端看不到视频按钮？

**可能原因**：
1. 患者未启用患者门户
2. 预约未正确关联
3. 预约时间不在允许范围内

**解决方法**：
- 确保患者已创建门户账号
- 检查预约关联
- 确认预约时间

### Q3: 视频连接失败？

**可能原因**：
1. 网络连接问题
2. 浏览器不支持 WebRTC
3. 防火墙阻止连接
4. API 配置错误

**解决方法**：
- 检查网络连接
- 使用支持的浏览器
- 检查防火墙设置
- 验证 API 配置

### Q4: 能否不使用 Comlink，自己搭建视频服务？

**理论上可以，但需要**：
- 实现 WebRTC 服务器
- 开发前端视频界面
- 集成到 OpenEMR 预约系统
- 处理安全性和合规性问题（HIPAA）
- 大量开发工作

**推荐**：如果预算允许，使用 Comlink Telehealth 是最简单、最合规的方案。

---

## 技术实现细节

### 相关文件位置

- **模块路径**: `/interface/modules/custom_modules/oe-module-comlink-telehealth/`
- **医生端 JS**: `public/assets/js/telehealth-calendar.js`
- **患者端 JS**: `public/assets/js/telehealth-patient.js`
- **视频会议室**: `src/Controller/TeleconferenceRoomController.php`
- **预约集成**: `src/Controller/TeleHealthCalendarController.php`

### 数据库表

- `comlink_telehealth_person_settings` - 用户 Telehealth 设置
- `comlink_telehealth_auth` - 认证信息
- `comlink_telehealth_appointment_session` - 预约会话关联

### API 端点

- `/modules/custom_modules/oe-module-comlink-telehealth/public/index.php` - 医生端入口
- `/modules/custom_modules/oe-module-comlink-telehealth/public/index-portal.php` - 患者端入口

---

## 替代方案

如果不想使用 Comlink Telehealth，可以考虑：

### 1. 集成第三方视频服务

- **Zoom**：集成 Zoom SDK
- **Google Meet**：通过链接集成
- **Microsoft Teams**：通过链接集成
- **Jitsi Meet**：开源视频会议系统（可自建）

### 2. 自定义开发

- 使用 WebRTC 技术开发自定义视频通话功能
- 需要大量开发和维护工作
- 需要处理安全性、合规性等问题

### 3. 使用预约中的链接字段

- 在预约中添加"视频链接"字段
- 医生创建预约时填入视频会议链接（Zoom、Meet等）
- 患者通过预约查看链接
- 手动管理，功能简单

---

## 总结

OpenEMR 的视频通话功能通过 **Comlink Telehealth** 模块实现，这是一个付费的第三方服务。

**使用流程**：
1. ✅ 安装并配置 Comlink Telehealth 模块
2. ✅ 注册医生和患者账号
3. ✅ 创建预约
4. ✅ 在预约时间前后2小时内，从日历或预约详情页面启动视频会话
5. ✅ 患者通过患者门户加入会话

**关键点**：
- ⏰ 只能在预约时间前后2小时内启动
- 👨‍⚕️ 医生需要注册 Telehealth 服务
- 💰 付费服务（约 $16/月/医生）
- 🔒 需要 HTTPS 和安全配置
- 📱 需要支持 WebRTC 的浏览器

---

**最后更新**: 2024
**适用版本**: OpenEMR 7.x+
**参考文档**: https://www.open-emr.org/wiki/index.php/Comlink_Telehealth
