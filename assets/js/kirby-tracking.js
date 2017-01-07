function logClientEvents(event_type) {
  event_type = event_type !== undefined ? event_type : '';

  var url = window.location.hostname + window.location.pathname + '/logthisevent';

  if(window.browserAndVersion === undefined)
    window.browserAndVersion = get_browser().name + ' ' + get_browser().version;
  if(window.userLang === undefined)
    window.userLang = navigator.language || navigator.userLanguage;

  var epochdate = ((new Date()).getTime() - ((new Date()).getTimezoneOffset() * 60000));
  var event_page = window.location.href;

  var data = {
    "epochdate" : epochdate,
    "browser" : window.browserAndVersion,
    "device" : window.navigator.userAgent,
    "event_page" : event_page,
    "event_type" : event_type,
    "lang" : window.userLang,
    "window_size" : window.innerWidth +'×'+ window.innerHeight,
  };

  if(window.isAdmin)
    console.log('Sending event to ' + url + ' with data ' + data.event_type);

  var request = $.ajax({
    type: "POST",
    url: url,
    data: data,
  });
}

// from http://stackoverflow.com/a/16938481
function get_browser() {
    var ua=navigator.userAgent,tem,M=ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
    if(/trident/i.test(M[1])){
        tem=/\brv[ :]+(\d+)/g.exec(ua) || [];
        return {name:'IE',version:(tem[1]||'')};
        }
    if(M[1]==='Chrome'){
        tem=ua.match(/\bOPR|Edge\/(\d+)/)
        if(tem!=null)   {return {name:'Opera', version:tem[1]};}
        }
    M=M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
    if((tem=ua.match(/version\/(\d+)/i))!=null) {M.splice(1,1,tem[1]);}
    return {
      name: M[0],
      version: M[1]
    };
 }
