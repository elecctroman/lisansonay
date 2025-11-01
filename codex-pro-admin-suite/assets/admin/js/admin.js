(function($){
$(function(){
if ( typeof window.codexProDashboard !== 'undefined' && $('#codex-pro-dashboard-chart').length ) {
var ctx = document.getElementById('codex-pro-dashboard-chart').getContext('2d');
new window.Chart(ctx, {
type: 'line',
data: {
labels: window.codexProDashboard.labels,
datasets: [
{
label: codexProDashboardStrings.credit,
borderColor: '#1d4ed8',
backgroundColor: 'rgba(29, 78, 216, 0.1)',
data: window.codexProDashboard.credit,
fill: true
},
{
label: strings.debit,
borderColor: '#dc2626',
backgroundColor: 'rgba(220, 38, 38, 0.1)',
data: window.codexProDashboard.debit,
fill: true
}
]
}
});
}
});
})(jQuery);
