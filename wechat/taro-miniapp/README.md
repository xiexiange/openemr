# OpenEMR 微信小程序（Taro + React）

## 项目说明

基于 Taro 3.6.13 + React 18 开发的微信小程序，实现医生注册和患者绑定功能。

## 安装依赖

```bash
npm install
```

## 开发命令

```bash
# 开发模式（监听文件变化，自动编译）
npm run dev:weapp

# 生产构建
npm run build:weapp
```

## 使用微信开发者工具

1. 运行 `npm run dev:weapp` 或 `npm run build:weapp`
2. 打开微信开发者工具
3. 选择"导入项目"
4. 项目目录选择：`wechat/taro-miniapp/dist`
5. AppID：`wx1bee5109fbe3e28e`（已在 project.config.json 中配置）

## 项目结构

```
taro-miniapp/
├── src/
│   ├── app.js              # 应用入口
│   ├── app.config.js       # 应用配置
│   ├── app.scss            # 全局样式
│   ├── pages/              # 页面目录
│   │   ├── index/          # 首页
│   │   ├── scan/           # 扫码页面
│   │   ├── doctor-register/ # 医生注册
│   │   └── patient-bind/   # 患者绑定
│   └── utils/              # 工具类
│       ├── api.js          # API 封装
│       ├── wechat.js      # 微信 API 封装
│       └── storage.js     # 本地存储
├── config/                 # Taro 配置
├── dist/                   # 编译输出目录（gitignore）
└── package.json
```

## API 配置

API 基础 URL 在 `src/utils/api.js` 中配置：

```javascript
const baseUrl = 'https://localhost/apis/default/api'
```

可根据实际情况修改。

## 注意事项

1. **OpenID 获取**：当前使用微信 `login()` 返回的 `code` 作为占位，需要后端提供 code -> openid 的接口
2. **编译输出**：编译后的代码在 `dist/` 目录，微信开发者工具需要打开这个目录
3. **开发模式**：`dev:weapp` 会监听文件变化自动重新编译

## 功能说明

- **首页**：选择医生注册或患者绑定
- **扫码**：扫描二维码，绑定微信，跳转到对应注册页面
- **医生注册**：填写医生信息完成注册
- **患者绑定**：填写患者信息并绑定到指定医生

## 相关文档

- [Taro 官方文档](https://taro.jd.com/)
- [React 官方文档](https://react.dev/)
