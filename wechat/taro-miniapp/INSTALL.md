# 安装说明

## 快速开始

```bash
# 1. 进入项目目录
cd /Users/xian/Documents/创业app/doctor/openemr/wechat/taro-miniapp

# 2. 安装依赖
npm install

# 3. 如果安装失败，尝试清除缓存后重新安装
rm -rf node_modules package-lock.json
npm cache clean --force
npm install

# 4. 开发模式
npm run dev:weapp

# 5. 在微信开发者工具中打开 dist 目录
```

## 常见问题

### 1. 找不到 @tarojs/react

如果提示找不到 `@tarojs/react`，请确保：
- 已运行 `npm install`
- package.json 中包含了 `@tarojs/react` 依赖

### 2. 编译错误

如果编译出错，尝试：
```bash
rm -rf node_modules dist
npm install
npm run dev:weapp
```

### 3. 依赖版本冲突

如果遇到版本冲突，可以尝试：
```bash
npm install --legacy-peer-deps
```

## 编译命令

- `npm run dev:weapp` - 开发模式（监听文件变化）
- `npm run build:weapp` - 生产构建

## 微信开发者工具配置

1. 打开微信开发者工具
2. 选择"导入项目"
3. 项目目录：`wechat/taro-miniapp/dist`
4. AppID：`wx1bee5109fbe3e28e`
