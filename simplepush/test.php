<script type="text/javascript" src="http://cdn.pubnub.com/pubnub-3.7.4.js"></script>
<script> 
         pubnub = PUBNUB.init({
              subscribe_key: 'sub-c-d5b4aaa4-1a8e-11e5-a5e2-02ee2ddab7fe',
              publish_key:   'pub-c-d85f66b8-863b-44b5-a312-10d5968cb38a',
          });       
          function sendPush() {
            // console.log(pubnub)
              pubnub.mobile_gw_provision ({
                  device_id: '1b41db8730535742f93c3285775f4c19ac93c61a568586a9dad05f2705582f2b', // Reg ID you got on your device
                  channel  : 'apns-test',
                  op: 'add', 
                  gw_type: 'apns',
                  error : function(msg){console.log(msg);},
                  callback : successCallback
              });
          }           
          function successCallback() {
            console.log("yes")
              var message = PNmessage();
           
              message.pubnub = pubnub;
              message.callback = function(msg){ console.log(msg); };
              message.error = function (msg){ console.log(msg); };
              message.channel = 'apns-test';
              message.apns = {
                  alert: '你好！'
              };
           
              message.publish();
          }          
          pubnub.subscribe({
            channel: 'apns-test', 
            message: function(msg) {
              console.log(msg)
            }
          })          

          function changeTemperature(temp) {
              // var temp = input.value;

            
              pubnub.publish({
                  channel: 'apns-test',
                  message: {
                      temperature: temp
                  }
              });
           
              if(temp >= 80) {
                  sendPush();
              }
          }  
</script>