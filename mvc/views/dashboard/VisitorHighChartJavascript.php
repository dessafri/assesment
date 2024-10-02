<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script type="application/javascript">
    
$(function() {
    LoadVisitor();
    function LoadVisitor()
    {
        $('#visitor').highcharts({
            chart: {
                type: 'line',
                height: 240
            },
            title: {
                text: '<?=$this->lang->line("dashboard_site_stats")?>'
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                categories: [
                    <?php
                        // foreach (array_keys($showChartVisitor) as $key => $v) {
                        //     echo "'".$v."'";
                        //     if(ends(array_keys($showChartVisitor)))
                        // }
                        echo "'" . implode("','", array_keys($showChartVisitor)) . "'";
                    ?>
                ],
                title: {
                    text: '<?=$this->lang->line("dashboard_date")?>',
                    align: 'low'
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: '<?=$this->lang->line("dashboard_visitors")?>',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y}</b>'
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                },
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function (e) {

                            }
                        }
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                verticalAlign: 'top',
                x: 5,
                y: -10,
                floating: true,
                borderWidth: 1,
                backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                shadow: true
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'Pengunjung',
                data: [
                    <?php
                        foreach ($showChartVisitor as $key => $visitors) {
                            echo "{y:".$visitors."},";
                            // if(isset($todaysAttendance[$key])) {
                            //     echo "{y:".$todaysAttendance[$key]['A'].", classID:'".$key."', 'dayWiseAttendance': '".json_encode($classWiseAttendance[$key])."', 'type': 'A'},";
                            // } else {
                            //      echo "{y:0},";
                            // }
                        }
                    ?>
                ],
                color: 'rgb(225,83,135)'
            }]
        });
    }

});


</script>
<script>
    var exam = <?php echo json_encode($exam)?>;
    var lapbulReport = <?php echo json_encode($lapbul_report); ?>;
    var verif = <?php echo json_encode($verif); ?>;
    var not_verif = <?php echo json_encode($not_verif); ?>;
    var responsible = <?php echo $responsible ?>;
    console.log(responsible);
    // alert(verif);
    // Data retrieved from https://gs.statcounter.com/browser-market-share#monthly-202201-202201-bar

// Create the chart

Highcharts.chart('container', {
    chart: {
        type: 'column' // Tipe chart batang
    },
    title: {
        text: 'Laporan Bulanan'
    },
    xAxis: {
        categories: lapbulReport
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Jumlah'
        }
    },
    series: [{
        name: 'Verified',
        data: verif
    }, {
        name: 'Not Verified',
        data: not_verif
    }]
});

</script>
<script>
    
    Highcharts.chart('container1', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Klengkapan Kinerja'
    },
    xAxis: {
        categories: exam
    },
    credits: {
        enabled: false
    },
    plotOptions: {
        column: {
            borderRadius: '1%'
        }
    },
    series: responsible
});
</script>
