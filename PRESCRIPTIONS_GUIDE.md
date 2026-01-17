# 医生门户 - Prescriptions（处方）使用指南

## 功能概述

OpenEMR 的处方功能允许医生为患者创建、查看、编辑和管理处方记录。处方功能通常在**就诊（Encounter）**过程中使用。

## 访问方式

### 方法一：通过就诊页面的 Coding 区域（推荐）

1. **选择患者**
   - 使用顶部菜单：`Patient → Finder` 或 `Patient → New/Search` 选择或创建患者

2. **创建或打开就诊记录（Encounter）**
   - 如果已有就诊记录：`Patient → Visits → Current` 
   - 如果需要创建新就诊：`Patient → Visits → Create Visit`

3. **访问 Coding 区域**
   - 在就诊页面左侧菜单中找到 **"Coding"** 链接
   - 点击进入 Coding 页面

4. **访问处方功能**
   - 在 Coding 页面中，找到 **"Prescriptions"** 部分
   - 有两个选项：
     - **"List Prescriptions"** - 查看患者的所有处方列表
     - **"Add Prescription"** - 添加新处方

### 方法二：直接访问 URL（需要患者 ID）

```
查看处方列表：
/controller.php?prescription&list&id={患者ID}

添加新处方：
/controller.php?prescription&edit&id=&pid={患者ID}
```

## 处方功能详细说明

### 1. 查看处方列表（List Prescriptions）

**功能**：查看患者的所有历史处方记录

**显示信息**：
- 处方编号
- 药品名称（Drug）
- 开药日期（Start Date）
- 开药医生（Provider）
- 数量（Quantity）
- 用法用量（Dosage）
- 频率（Interval）
- 处方状态（Active/Inactive）
- 是否已配药（Filled）

**操作**：
- 查看处方详情
- 编辑处方
- 删除处方（如果权限允许）
- 打印处方
- 发送电子处方（如果启用了 e-Rx）

### 2. 添加新处方（Add Prescription）

**必需字段**：
- **Drug**（药品名称）- 可以手动输入或从药品库选择
- **Start Date**（开始日期）- 默认当前日期
- **Provider**（开药医生）- 默认当前登录用户

**可选字段**：
- **Drug ID** - 如果启用了院内药房（In-house Pharmacy），可以从库存药品中选择
- **Form**（剂型）- 片剂、胶囊、液体等
- **Dosage**（用法）- 用法说明
- **Size**（规格/剂量）- 如 10mg, 50mg 等
- **Unit**（单位）- mg, ml, 片等
- **Route**（给药途径）- 口服、注射、外用等
- **Interval**（频率）- 每天几次、每几小时一次等
- **Quantity**（数量）- 总数量
- **Refills**（续药次数）- 允许续药几次
- **Per Refill**（每次续药数量）
- **Substitute**（是否允许替代）- 是否允许药剂师使用替代药品
- **Note**（备注）- 处方备注信息
- **Pharmacy**（药房）- 选择的药房
- **Diagnosis**（诊断）- 关联的诊断代码
- **Usage Category**（使用类别）- 门诊、住院等
- **Request Intent**（请求意图）- 订单、计划等

**药品选择方式**：

1. **手动输入药品名称**
   - 直接在 "Drug" 字段输入药品名称
   - 如果系统中有匹配的药品，会自动提示

2. **从药品库选择（如果启用了 In-house Pharmacy）**
   - 在 "Drug ID" 下拉菜单中选择
   - 选择后会自动填充药品的相关属性（剂型、规格、单位等）

3. **使用 RxNorm / RxCUI 代码**
   - 如果系统配置了 RxNorm 数据库
   - 可以搜索标准化的药品代码

4. **使用药品模板（Drug Templates）**
   - 如果之前创建过药品模板
   - 可以从模板快速创建处方

**保存和后续操作**：
- **Save** - 保存处方
- **Save and Dispense** - 保存并立即配药（如果启用了院内药房）
- **Print** - 打印处方
- **Send** - 发送电子处方（如果启用了 e-Rx）
- **Fax** - 传真处方到药房（如果配置了传真功能）

## 权限要求

要使用处方功能，用户需要以下 ACL 权限：
- **patients** → **rx**（读写权限）

如果没有权限，菜单中的处方选项将不显示。

## 全局设置

处方功能的显示受以下全局设置控制：
- **`disable_prescriptions`** - 如果设置为 `1`，将禁用处方功能
  - 检查位置：`Admin → Globals → Features`

## 相关功能

### 电子处方（e-Rx）

如果启用了 NewCrop 电子处方服务：
- 可以通过菜单：`New Crop → e-Rx` 访问
- 需要配置 NewCrop API 凭据
- 支持电子处方传输和续药请求

### 院内药房（In-house Pharmacy）

如果启用了院内药房功能：
- 可以从库存药品中选择
- 支持配药操作（Dispense）
- 可以管理药品库存
- 支持药品销售和报告

**启用方法**：`Admin → Globals → Features → "In-house Pharmacy"`

### 处方报告

查看处方统计和报告：
- `Reports → Clients → Rx` - 患者处方报告

## 常见操作流程

### 完整处方流程示例

1. **选择患者**
   ```
   Patient → Finder → 选择患者
   ```

2. **创建就诊记录**
   ```
   Patient → Visits → Create Visit
   ```

3. **添加处方**
   ```
   就诊页面左侧 → Coding → Prescriptions → Add Prescription
   ```

4. **填写处方信息**
   - 选择或输入药品名称
   - 设置用法用量
   - 设置数量和续药次数
   - 选择药房（可选）
   - 添加备注（可选）

5. **保存处方**
   - 点击 "Save" 保存
   - 或点击 "Save and Print" 保存并打印
   - 或点击 "Save and Send" 发送电子处方

6. **查看处方列表**
   ```
   Coding → Prescriptions → List Prescriptions
   ```

## 技术实现

### 相关文件

- **控制器**：`controllers/C_Prescription.class.php`
- **模板**：`templates/prescription/general_edit.html.twig`
- **处方类**：`library/classes/Prescription.class.php`
- **数据库表**：`prescriptions`
- **访问入口**：`/controller.php?prescription&...`

### URL 路由

```
查看列表：/controller.php?prescription&list&id={pid}
添加新处方：/controller.php?prescription&edit&id=&pid={pid}
编辑处方：/controller.php?prescription&edit&id={prescription_id}&pid={pid}
```

## 注意事项

1. **必须先选择患者**：处方功能需要患者 ID（pid）才能工作

2. **关联就诊记录**：处方通常会关联到就诊记录（encounter），建议在就诊过程中创建处方

3. **药品数据**：
   - 如果没有启用院内药房，只能手动输入药品信息
   - 启用院内药房后，可以从库存中选择标准化的药品

4. **权限检查**：
   - 确保用户有 `patients.rx` 权限
   - 某些操作可能需要额外的权限

5. **处方状态**：
   - Active（活跃）：当前有效的处方
   - Inactive（非活跃）：已停用或过期的处方

6. **数据完整性**：
   - 建议填写完整的处方信息，包括用法用量、频率等
   - 关联诊断代码有助于病历完整性

---

**最后更新**：2024
**适用版本**：OpenEMR 7.x+
