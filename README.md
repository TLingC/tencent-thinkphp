# 腾讯云 ThinkPHP Serverless Component

## 简介

腾讯云 [ThinkPHP](https://github.com/top-think/framework) Serverless Component。

本项目基于 [tencent-laravel](https://github.com/serverless-components/tencent-laravel) 修改，以支持运行 ThinkPHP，同时优化了对静态资源的兼容性。

## 目录

0. [准备](#0-准备)
1. [安装](#1-安装)
1. [配置](#2-配置)
1. [部署](#3-部署)
1. [移除](#4-移除)

### 0. 准备

#### 初始化 ThinkPHP 项目

在使用此组件之前，你需要先自己初始化一个 `thinkphp` 项目

```shell
composer create-project topthink/think serverless-thinkphp
```

#### 修改 ThinkPHP 项目

由于云函数在执行时，只有 `/tmp` 可读写的，本component在entry中已经设置了相应的运行目录，故运行目录不需要进行额外的修改。

另外，由于云函数是无状态的，因此cache、session等都建议移至外部保存。

### 1. 安装

通过 npm 全局安装 [serverless cli](https://github.com/serverless/serverless)

```shell
$ npm install -g serverless
```

### 2. 配置

在项目根目录，创建 `serverless.yml` 文件，在其中进行如下配置

```shell
$ touch serverless.yml
```

```yml
# serverless.yml

MyComponent:
  component: '@tlingc/tencent-thinkphp'
  inputs:
    region: ap-guangzhou
    functionName: thinkphp-function
    code: ./
    functionConf:
      timeout: 10
      memorySize: 128
      environment:
        variables:
          TEST: vale
      vpcConfig:
        subnetId: ''
        vpcId: ''
    apigatewayConf:
      protocols:
        - https
      environment: release
```

- 除component名称外，本项目配置项与 tencent-laravel 一致，查看 [更多配置](https://github.com/serverless-components/tencent-laravel/tree/master/docs/configure.md)

### 3. 部署

> 注意：**在部署前，你需要先清理本地运行的配置缓存，执行 `php think clear` 即可。**

如您的账号未 [登陆](https://cloud.tencent.com/login) 或 [注册](https://cloud.tencent.com/register) 腾讯云，您可以直接通过 `微信` 扫描命令行中的二维码进行授权登陆和注册。

通过 `sls` 命令进行部署，并可以添加 `--debug` 参数查看部署过程中的信息

```shell
$ sls --debug
```

> 注意: `sls` 是 `serverless` 命令的简写。

### 4. 移除

通过以下命令移除部署的 API 网关

```shell
$ sls remove --debug
```

### 账号配置（可选）

当前默认支持 CLI 扫描二维码登录，如您希望配置持久的环境变量/秘钥信息，也可以本地创建 `.env` 文件

在 `.env` 文件中配置腾讯云的 SecretId 和 SecretKey 信息并保存

如果没有腾讯云账号，可以在此 [注册新账号](https://cloud.tencent.com/register)。

如果已有腾讯云账号，可以在 [API 密钥管理](https://console.cloud.tencent.com/cam/capi) 中获取 `SecretId` 和`SecretKey`.

```text
# .env
TENCENT_SECRET_ID=123
TENCENT_SECRET_KEY=123
```

### 更多组件

可以在 [Serverless Components](https://github.com/serverless/components) repo 中查询更多组件的信息。