## 聊天室

使用php 5.6 + [swoole 1.7.19](http://www.swoole.com/)开发的聊天室。

### 服务端
```
php serve.php [-d]

[-d] 后台作为守护进程运行

```

### 客户端
```
php client.php -u hsinlu -p hsinlu [-r]

-u   用户名
-p   密码
[-r] 是否接受客户端指令，如果传递此参数将只接收服务端发来的数据
```

#### 获取在线列表
```
list
```

#### 获取消息记录
```
messages
```

#### 发送消息
```
// 发送公共消息
chat::{message}

// 发送频道消息
chat:channel={channel}:{message}

// 发送私聊消息
chat:fd={fd}:{message}

```

### 通讯协议
所有消息传递均采用json格式

#### 登录

##### 客户端发送
```
{"type": "login", "username": "hsinlu", "password": "hsinlu"}
```
>>目前服务端未做完整的用户验证，username与password一致即可登录。

##### 服务端回复
```
// 登录成功
{"type": "login", "success": "true"}
// 登录失败
{"type": "login", "errors": ["用户名和密码不正确。"]}
```

#### 加入聊天室通知

##### 服务端回复
```
{"type":"join","fd":4,"username":"si"}
```

#### 退出聊天室通知

##### 服务器回复
```
{"type":"leave","fd":3,"username":"si"}
```

#### 获取在线列表

##### 客户端发送
```
{"type": "list"}
```

##### 服务端回复
```
{"type":"list","users":[{"fd":2,"username":"si","channel":"public","is_online":1},{"fd":3,"username":"xin","channel":"public","is_online":1}]}
```


#### 获取我的历史消息

##### 客户端发送
```
{"type":"messages"}
```

##### 服务端回复
```
{"type":"list","users":[{"fd":2,"username":"si","channel":"public","is_online":1},{"fd":3,"username":"xin","channel":"public","is_online":1}]}
```


#### 消息发送

##### 客户端发送
```
// 公共频道
{"type":"chat","message":"hello everyone.","id":"670969e2d418a2249508e7763f84eaf6"}

// 指定频道
{"type":"chat","to_channel":"public","message":"hello everyone.","id":"963ad9f2842f867e05712ea48b8ca4e5"}

// 私聊
{"type":"chat","to_fd":"3","message":"hello","id":"3f194080e87e66236ae917d6aec7f9c8"}
```
>>id为客户端生成的消息唯一标识

#### 消息接收

##### 服务端回复
```
{"type":"chat","from_fd":4,"from_username":"si","message":"hello everyone."}
```

#### 消息发送成功

##### 服务端回复
```
{"type":"chat","success":true,"id":"670969e2d418a2249508e7763f84eaf6"}
```
>>id为客户端发送的消息唯一标识，服务端回复指定的id的消息是否发送成功。

