
var skype = {
    // configs
    room: '#live:remigiusz.jackowski/$227d36e668331949'

    // functions
    ,f_read: function() {
        var buf = '';
        process.stdin.setEncoding('utf8');
        process.stdin.resume();
        process.stdin.on('data', function(b) {buf += b.toString();});
        process.stdin.on('end', function() {
           return buf;
        });
    }
    ,f_filter: function(msg) {
        return msg.replace(/\033/g, "").replace(/\[[0-9]*m/g, '');
    }
    ,f_send: function(room, msg) {
        require('skype-dbus').createClient('MensaBroadcaster', null, function (err, skype) {
            var cmd = "CHATMESSAGE " + room + " " + msg;
            skype.send(cmd, function (a, b, c) {
                console.log(b);
                exit();
            });
        });
    }
};

skype.f_send(
    skype.room, skype.f_filter(
        skype.f_read()));

// or as you would write in Elixir
// read |> filter |> send(room)

/**

http://kirils.org/skype/stuff/pdf/2013/SkypeSDK.pdf

OPEN CHAT #live:remigiusz.jackowski/$227d36e668331949
  opens chat

OPEN IM #live:remigiusz.jackowski/$227d36e668331949 test-msg
  opens chat with test-msg preinputted

CHATMESSAGE #live:remigiusz.jackowski/$227d36e668331949 test-msg
  sends `test-msg` to selected chat

SEARCH RECENTCHATS
  searches for recently used chats

SEARCH ACTIVECHATS
  returns name of chat you currently have focused. If returns null, this chat cannot be used in Skype API

CREATE CHAT
  creates new multichat and returns handle



Warning, not every group is equal. Group created in Skype client may not be listable
  in Skype API. If you cannot find group using SEARCH ACTIVECHATS, you need to create one
  using `CREATE CHAT` and then `OPEN CHAT <string returned by CREATE CHAT>`.

**/

