/bin/workermand start


cd workerman-game/applications/Game/Tests/
运行 php gameChat.php
gamechat 是个聊天的例子，可以根据这个例子来


主要是用Game/Event.php里的几个方法。



大概看了下，他是用的多进程，用了libevent扩展，pcntl扩展；

有个进程用libevent接收客户端来的消息，然后通过udp发给处理worker(GameWorker.php);

有个关键的地方我不懂，就是收到消息发给worker之后，worker处理后会把结果消息通过udp传回来，他用了libevent的一些我不了解的功能，我不知道什么作用；所以在worker处理的时候，libevent所在线程是不是需要等待workder线程，如果是的话，显然这个框架就不好。

另外他好像好用到了共享内存，用来干什么我也不知道，共享内存我大概知道，深入的用法我不了解。

他用的是进程，那么进程之间的数据共享怎么实现似乎没有，这样的话如果用户之间需要交互的话，他这个可能实现起来就很困难。

不过至少比起http方式来，效率要高很多，因为单个进程始终是存在的，每个进程读取的数据是不用像http那种每次用请求都从数据库读，完毕了要写回数据库


雷迅  18:14:47
我说那个关键问题，你去看看GameGateway.php 92行：$this->event->add($this->innerMainSocket,  Man\Core\Events\BaseEvent::EV_READ, array($this, 'recvUdp'));

这个到底起什么作用
我不想去研究
雷迅  18:23:09
确实是可以实现异步
雷迅  18:24:31
就是不用等待worker计算，而是worker执行完发数据过来时，自动调用处理函数
雷迅  18:25:39
那他这个在没有线程的时代，应该是最好的方式了；

可能稍微可以改进的是libevent和worker的通讯，如果是单机的话，用消息队列应该更快
雷迅  18:27:13
用udp话，可以多台服务器负载均衡，对于玩家之间联系不多的他这个很好
联系多的话，要看具体业务了
