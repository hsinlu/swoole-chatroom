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
chat::{content}

// 发送频道消息
chat:channel={channel}:{content}

// 发送私聊消息
chat:fd={fd}:{content}

```

### 通讯协议
所有消息传递均采用json格式

#### 登录

##### 客户端发送
```
["login",{"username":"hsinlu","password":"hsinlu"}]
```
>>目前服务端未做完整的用户验证，username与password一致即可登录。

##### 服务端回复
```
// 登录成功
["login",{"success":true}]
// 登录失败
["login",{"errors":["用户名和密码不正确。"]}]
```

#### 加入聊天室通知

##### 服务端回复
```
["join",{"fd":4,"username":"hsinlu"}]
```

#### 退出聊天室通知

##### 服务器回复
```
["leave",{"fd":4,"username":"hsinlu"}]
```

#### 获取在线列表

##### 客户端发送
```
["list"]
```

##### 服务端回复
```
["list",[{"fd":3,"username":"si","channel":"public","is_online":1},{"fd":4,"username":"hsinlu","channel":"public","is_online":1}]]
```

#### 获取我的历史消息

##### 客户端发送
```
["messages"]
```

##### 服务端回复
```
["messages",[{"fd":1,"from_fd":2,"content":"hello","channel":"whisper","time":"1443884147","is_readed":0},{"fd":1,"from_fd":2,"content":"hello si","channel":"whisper","time":"1443884264","is_readed":0}]]
```


#### 消息发送

##### 客户端发送
```
// 公共频道
["chat",{"content":"hello","id":"2b1dc0c605d45cee8887e07e8188795e"}]

// 指定频道
["chat",{"to_channel":"public","content":"hello","id":"2b1dc0c605d45cee8887e07e8188795e"}]

// 私聊
["chat",{"to_fd":"1","content":"hello si","id":"7a1c7714b34897fac377fb1213eab409"}]
```
>>id为客户端生成的消息唯一标识

#### 消息接收

##### 服务端回复
```
["chat",{"from_fd":2,"from_username":"hsinlu","content":"hello"}]
```

#### 消息发送成功

##### 服务端回复
```
["chat",{"success":true,"id":"2b1dc0c605d45cee8887e07e8188795e"}]
```
>>id为客户端发送的消息唯一标识，服务端回复指定的id的消息是否发送成功。

