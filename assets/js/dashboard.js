"use strict";

// Class definition
var DashboardWidgets = function() {
	
	var _initChartsDocumentProcessing = function() {
        var element = document.getElementById("document-processing");

        if (!element) {
            return;
        }
		
		var colors = DocumentProcessingChartData.colors.filter(function (el) { 
			return el != ''; 
		});

        var options = {
            series: DocumentProcessingChartData.series,
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: ['60%'],
                    endingShape: 'rounded'
                },
            },
            legend: {
                show: true
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: DocumentProcessingChartData.categories,
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: "#B5B5C3",
                        fontSize: '12px',
                        fontFamily: "Poppins"
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: "#B5B5C3",
                        fontSize: '12px',
                        fontFamily: "Poppins"
                    },
					formatter: function (value) {
						return "$" + value;
					}
                }
            },
            fill: {
                opacity: 1
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px',
                    fontFamily: "Poppins"
                },
                y: {
                    formatter: function(val) {
                        return "$" + val
                    }
                }
            },
            grid: {
                borderColor: "#ECF0F3",
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };
		
		if (colors.length > 0) { 
			options['colors'] = DocumentProcessingChartData.colors;
		}

        var chart = new ApexCharts(element, options);
        chart.render();
    }
	
	var _initChartsSuppliers = function() {
		var element = document.getElementById("suppliers-chart");

        if (!element) {
            return;
        }
		
		var colors = SupplierChartData.colors.filter(function (el) { 
			return el != ''; 
		});
		
		var options = {
				series: SupplierChartData.series,
				chart: {
					width: 600,
					type: 'pie',
				},
				labels: SupplierChartData.labels,
				tooltip: {
					style: {
						fontSize: '12px',
						fontFamily: "Poppins"
					},
					y: {
						formatter: function(val) {
							return "$" + val
						}
					}
				},
				legend: {
					position: 'bottom'
				},
				responsive: [{
					breakpoint: 480,
					options: {
						chart: {
							width: 200
						},
						legend: {
							position: 'bottom'
						}
					}
				}]
			};

		if (colors.length > 0) {
			options['colors'] = SupplierChartData.colors;			
		}
		
        var chart = new ApexCharts(element, options);
        chart.render();
	}
	
	// Public methods
    return {
        init: function() {
			// Charts Widgets
			_initChartsDocumentProcessing();
			_initChartsSuppliers();
		}
	}
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = DashboardWidgets;
}

jQuery(document).ready(function() {
    DashboardWidgets.init();
});
