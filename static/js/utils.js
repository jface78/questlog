
function formatFullDate(dateString) {
  var d = new Date(parseInt(dateString)*1000);
  return d.toLocaleDateString() + ' at ' + d.toLocaleTimeString();
}