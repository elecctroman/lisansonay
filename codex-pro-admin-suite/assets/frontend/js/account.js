(function(){
if ( typeof window.codexProAccount === 'undefined' ) {
return;
}

var canvas = document.getElementById('codex-account-chart');

if ( ! canvas || ! window.Chart ) {
return;
}

var ctx = canvas.getContext('2d');

new window.Chart(ctx, {
type: 'line',
data: {
labels: window.codexProAccount.labels,
datasets: [
{
label: 'Credit',
borderColor: '#2563eb',
backgroundColor: 'rgba(37, 99, 235, 0.1)',
data: window.codexProAccount.credit,
fill: true
},
{
label: 'Debit',
borderColor: '#dc2626',
backgroundColor: 'rgba(220,38,38,0.1)',
data: window.codexProAccount.debit,
fill: true
}
]
}
});
})();
