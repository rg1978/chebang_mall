var wxShare = function (shareData){
  var ua=window.navigator.userAgent;
  var reg=/MicroMessenger/i;
  var iswechat=reg.test(ua);
  var url=document.location.href;

  if (iswechat) {
      $.ajax({
        url: 'wxshare.html?url='+url,
        type: 'get',
        dataType: 'json',
        success: function(rs) {
        var appId = rs.appId;
        var timestamp = rs.timestamp;
        var nonceStr = rs.nonceStr;
        var signature = rs.signature;
        wx.config({
            debug: false,
            appId: appId,
            timestamp: timestamp,
            nonceStr: nonceStr,
            signature: signature,
            jsApiList: [
              'checkJsApi',
              'onMenuShareTimeline',
              'onMenuShareAppMessage',
              'onMenuShareQQ',
              'onMenuShareWeibo',
              'onMenuShareQZone',
            ]
        });
        wx.ready(function () {
          //分享到朋友圈
          wx.onMenuShareTimeline(shareData);
          //发送给朋友
          wx.onMenuShareAppMessage(shareData);
          //分享到QQ
          wx.onMenuShareQQ(shareData);
          //分享到腾讯微博
          wx.onMenuShareWeibo(shareData);
          //分享到QQ空间
          wx.onMenuShareQZone(shareData);
        });
        }
      });
   }
}

    
