<!DOCTYPE html>
<?php
//include stuff here

require("../inc/all.php");

?>
<html>
  <head>
    <meta charset="utf-8">
    <title>Project Submarino</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="asset/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
    <link href="asset/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="asset/bootstrap/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="bootstrap/ico/apple-touch-icon-114-precomposed.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="bootstrap/ico/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="bootstrap/ico/apple-touch-icon-57-precomposed.png">
                                   <link rel="shortcut icon" href="bootstrap/ico/favicon.png">
  </head>
  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">Project Submarino</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li><a href="index.php">Home</a></li>
              <li><a href="index.php">Leagues</a></li>
              <li class="active"><a href="team.php?team_name=Arsenal%20Football%20Club&team_id=660">Teams</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">

      <!-- Main hero unit for a primary marketing message or call to action -->
      <div class="hero-unit">
	  		  <h1>Team: <?php echo($_GET["team_name"]) ?></h1>
		 <p>How are injuries distributed across dimensions of games missed and type.</p>
		 
	  <div class="row-fluid">
        <div class="columnA pull-left">

			<div class="chart"></div>
		
		
		
		
	<script type="text/javascript">

      // This example draws horizontal bar charts…
      // Created by Frank Guerino : "http://www.guerino.net"

    // Data Used for this example…
    <?php  
        $teamId = $_GET["team_id"];
        $teamDao = new TeamDao(); 
        $leagueGamesMissedByPlayer = $teamDao->getGamesMissedByPlayer($teamId);
        $numInjuriesByGamesMissed = $teamDao->getNumInjuriesByGamesMissed($teamId);
        //$injuryDataByPlayer = $teamDao->getInjuryDataByPlayerAndType($teamId);
    ?>
    // main graph dataset
    
    
    /*var dataSet1 = [
      {Team: "Arsenal", GamesLost: 54},
	  {Team: "Liverpool", GamesLost: 52},
      {Team: "Manchester United", GamesLost: 50},
	  {Team: "Manchester City", GamesLost: 48},
	  {Team: "Fulham", GamesLost: 33},
	  {Team: "Everton", GamesLost: 30},
	  {Team: "Blackpool", GamesLost: 28},
	  {Team: "Bolton", GamesLost: 28},
	  {Team: "Chelsea", GamesLost: 27},
	  {Team: "Spurs", GamesLost: 24},
	  {Team: "Crystal Palace", GamesLost: 23},
	  {Team: "Sunderland", GamesLost: 22},
      {Team: "Newcastle", GamesLost: 21},
	  {Team: "Stoke", GamesLost: 21},
	  {Team: "West Ham", GamesLost: 19},
	  {Team: "QPR", GamesLost: 18},
	  {Team: "Swansea", GamesLost: 10},
	  {Team: "Norwich", GamesLost: 9},
	  {Team: "Aston Villa", GamesLost: 9},
	  {Team: "West Brom", GamesLost: 8}
	  ];
     */

      function drawHorizontalBarChart(chartID, dataSet, selectString, bWidthTotal, cWidth) {
		
        // chartID => A unique drawing identifier that has no spaces, no "." and no "#" characters.
        // dataSet => Input Data for the chart, itself.
        // selectString => String that allows you to pass in
        //           a D3 select string.

        var canvasWidth = cWidth;
        var barsWidthTotal = bWidthTotal;
        var barHeight = 20;
        var barsHeightTotal = barHeight * dataSet.length;
        //var canvasHeight = 200;
        var canvasHeight = dataSet.length * barHeight + 10; // +10 puts a little space at bottom.
        var legendOffset = barHeight/2;
        var legendBulletOffset = 30;
        var legendTextOffset = 20;

        var x = d3.scale.linear().domain([0, d3.max(dataSet, function(d) { return d.GamesLost; })]).rangeRound([0, barsWidthTotal]);
        var y = d3.scale.linear().domain([0, dataSet.length]).range([0, barsHeightTotal]);


        var synchronizedMouseOver = function() {
          var bar = d3.select(this);
          var indexValue = bar.attr("index_value");

          var barSelector = "." + "bars-" + chartID + "-bar-" + indexValue;
          var selectedBar = d3.selectAll(barSelector);
          selectedBar.style("fill", "Goldenrod");

          var textSelector = "." + "bars-" + chartID + "-legendText-" + indexValue;
          var selectedLegendText = d3.selectAll(textSelector);
          selectedLegendText.style("fill", "Goldenrod");
        };

        var synchronizedMouseOut = function() {
          var bar = d3.select(this);
          var indexValue = bar.attr("index_value");

          var barSelector = "." + "bars-" + chartID + "-bar-" + indexValue;
          var selectedBar = d3.selectAll(barSelector);
          var colorValue = selectedBar.attr("color_value");
          selectedBar.style("fill", "steelblue");

          var textSelector = "." + "bars-" + chartID + "-legendText-" + indexValue;
          var selectedLegendText = d3.selectAll(textSelector);
          selectedLegendText.style("fill", "steelblue");
        };

      // Create the svg drawing canvas…
      var canvas = d3.select(selectString)
        .append("svg:svg")
          //.style("background-color", "yellow")
          .attr("width", canvasWidth)
          .attr("height", canvasHeight);

      // Draw individual hyper text enabled bars…
      canvas.selectAll("rect")
        .data(dataSet)
        .enter().append("svg:a")
          .attr("xlink:href", function(d) { return d.Team; })
          .append("svg:rect")
            // NOTE: the "15 represents an offset to allow for space to place magnitude
            // at end of bars.  May have to address this better, possibly by placing the
            // magnitude within the bars.
            //.attr("x", function(d) { return barsWidthTotal - x(d.magnitude) + 15; }) // Left to right
            .attr("x", 200) // Right to left
            .attr("y", function(d, i) { return y(i); })
            .attr("height", barHeight)
            .on('mouseover', synchronizedMouseOver)
            .on("mouseout", synchronizedMouseOut)
            .style("fill", "White" )
            .style("stroke", "White" )
            .transition()
              .duration(1500)
              .delay(function(d, i) { return i * 100; })
            .attr("width", function(d) { return x(d.GamesLost); })
            .style("fill", "steelblue" )
            .attr("index_value", function(d, i) { return "index-" + i; })
            .attr("class", function(d, i) { return "bars-" + chartID + "-bar-index-" + i; })
            .attr("color_value", "steelblue") // Bar fill color…
            .style("stroke", "white"); // Bar border color…


      // Create text values that go at end of each bar…
      canvas.selectAll("text")
        .data(dataSet) // Bind dataSet to text elements
        .enter().append("svg:text") // Append text elements
          .attr("x", x)
          .attr("y", function(d, i) { return y(i); })
          //.attr("y", function(d) { return y(d) + y.rangeBand() / 2; })
          .attr("dx", function(d) { return x(d.GamesLost) +195; })
          .attr("dy", barHeight-5) // vertical-align: middle
          .attr("text-anchor", "end") // text-align: right
          .text(function(d) { return d.GamesLost;})
          .attr("fill", "White");

      // Create hyper linked text at right that acts as label key…
      canvas.selectAll("a.legend_link")
        .data(dataSet) // Instruct to bind dataSet to text elements
        .enter().append("svg:a") // Append legend elements
          .attr("xlink:href", function(d) { return d.Team; })
            .append("text")
              .attr("text-anchor", "center")
              .attr("x", 0)
              //.attr("y", function(d, i) { return legendOffset + i*20 - 10; })
              .attr("y", function(d, i) { return legendOffset + i*barHeight; } )
              .attr("dx", 0)
              .attr("dy", "5px") // Controls padding to place text above bars
              .text(function(d) { return d.Team;})
              .style("fill", "steelblue")
              .attr("index_value", function(d, i) { return "index-" + i; })
              .attr("class", function(d, i) { return "bars-" + chartID + "-legendText-index-" + i; })
              .on('mouseover', synchronizedMouseOver)
              .on("mouseout", synchronizedMouseOut);

      };

    </script>

<script src="http://d3js.org/d3.v3.js"></script>
<script>

var width = 600,
    height = 600,
    outerRadius = Math.min(width, height) / 2 - 10,
    innerRadius = outerRadius - 24;

//var formatPercent = d3.format(".1%");

var arc = d3.svg.arc()
    .innerRadius(innerRadius)
    .outerRadius(outerRadius);

var layout = d3.layout.chord()
    .padding(.04)
    .sortSubgroups(d3.descending)
    .sortChords(d3.ascending);

var path = d3.svg.chord()
    .radius(innerRadius);

var svg = d3.select(".chart").append("svg")
    .attr("width", width)
    .attr("height", height)
  .append("g")
    .attr("id", "circle")
    .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

svg.append("circle")
    .attr("r", outerRadius);

d3.csv("names.csv", function(cities) {
  d3.json("data.json", function(matrix) {
 // [ [0,0,0,4,0,0], [0,0,0,5,2,0], [0,0,0,7,0,3], [4,5,7,0,0,0], [0,2,0,0,0,0], [0,0,3,0,0,0] ]
    // Compute the chord layout.
    layout.matrix(matrix);

    // Add a group per neighborhood.
    var group = svg.selectAll(".group")
        .data(layout.groups)
      .enter().append("g")
        .attr("class", "group")
        .on("mouseover", mouseover);

    // Add a mouseover title.
   group.append("title").text(function(d, i) {
     //return cities[i].name + ": " + formatPercent(d.value) + " of origins";
     return "Total Games missed due to " + cities[i].name + " injuries : " + Math.round(d.value) ;
   });

    // Add the group arc.
    var groupPath = group.append("path")
        .attr("id", function(d, i) { return "group" + i; })
        .attr("d", arc)
        .style("fill", function(d, i) { return cities[i].color; });

    // Add a text label.
    var groupText = group.append("text")
        .attr("x", 6)
        .attr("dy", 15);

    groupText.append("textPath")
        .attr("xlink:href", function(d, i) { return "#group" + i; })
        .text(function(d, i) { return cities[i].name; });

    // Remove the labels that don't fit. :(
    groupText.filter(function(d, i) { return groupPath[0][i].getTotalLength() / 2 - 16 < this.getComputedTextLength(); })
        .remove();

    // Add the chords.
    var chord = svg.selectAll(".chord")
        .data(layout.chords)
      .enter().append("path")
        .attr("class", "chord")
        .style("fill", function(d) { return cities[d.source.index].color; })
        .attr("d", path);

    // Add an elaborate mouseover title for each chord.
    chord.append("title").text(function(d) {
      return cities[d.source.index].name
          + " missed "
          + d.source.value 
          + " games due to "
          + cities[d.target.index].name
          + " injuries ";
          
         // + ": " + formatPercent(d.source.value)
         // + ": " + d.source.value
         
          //+ "\n" + cities[d.target.index].name
         // + " ? " + cities[d.source.index].name
         //+ ": " + formatPercent(d.target.value);
          //+ ": " + d.target.value;
    });

    function mouseover(d, i) {
      chord.classed("fade", function(p) {
        return p.source.index != i
            && p.target.index != i;
      });
    }
  });
});

</script>

    

    <STYLE type="text/css">
      div.div_Header {
	width: 100%;
	border:2px solid White;
	border-radius:7px;
	background: WhiteSmoke;
	font: bold 14px Arial;
	font-family:Arial, Helvetica, sans-serif;
	color:WhiteSmoke;
	text-align:center;
      }
      h1.h1_BodyHeader {
	text-align:center;
	font: bold 1.5em Arial;
      }
      h2.h2_LeftMenuHeader {
	text-align:center;
	font: bold 1.2em Arial;
      }
      h3.h3_Body {
        text-align:center;
      }
      p.p_Red {
        color:Red;
      }
      table.table_Header {
	width: 100%;
	text-align:center;
      }
      td.td_HeaderLeft {
	text-align:left;
      }
      td.td_HeaderRight {
	text-align:right;
      }
      div.div_Menu {
	width: 100%;
	border:2px solid White;
	border-radius:7px;
	background: MidnightBlue;
	font: bold 14px Arial;
	font-family:Arial, Helvetica, sans-serif;
	color:White;
	text-align:center;
      }
      p.p_Left {
	font-family:Arial, Helvetica, sans-serif;
	color:Black;
        text-align:left;
        padding-left: 5px;
        font: normal 14px Arial;
      }
      table.table_Body {
	width: 100%;
	height: 100%;
	padding: 0;
      }
      td.td_BodyLeft {
	width: 250px;
	height: 100%;
	padding: 0;
      }
      li.li_LeftMenu {
	text-align:left;
	font: normal 14px Arial;
      }
      a.a_LeftMenuNoUnderLine {
	text-decoration:  none;
      }
      div.div_Body {
	height: 100%;
	width: 100%;
	position: relative;
	border:2px solid White;
	border-radius:7px;
	background: WhiteSmoke;
	font: bold 14px Arial;
	font-family:Arial, Helvetica, sans-serif;
	color:Black;
	text-align:center;
      }
      div.div_Footer {
	width: 100%;
	border:2px solid White;
	border-radius:7px;
	background: MidnightBlue;
	font: bold 14px Arial;
	font-family:Arial, Helvetica, sans-serif;
	color:White;
	text-align:center;
      }
      p.p_if4itMessage {
	width: 100%;
	background: White;
	font: bold .75em Arial;
	font-family:Arial, Helvetica, sans-serif;
	color:GoldenRod;
	text-align:center;
      }
      .menuButton{
        background-color: MidnightBlue;
      }
      .menuButton li{
	height: 100%;
	list-style: none;
	display: inline;
      }
      .menuButton li a{
	height: 100%;
	padding: 3px 0.5em;
	text-decoration: none;
	color: White;
	background-color: MidnightBlue;
	border: 2px solid MidnightBlue;
      }
      .menuButton li a:hover{
	height: 100%;
	color: MidnightBlue;
	background-color: White;
	border-style: outset;
	background-color: White;
      }
      .menuButton li a:active{
        height: 100%;
        border-style: inset;
        color: MidnightBlue;
        background-color: White;
      }
      .menuButton li a.disabled{
        height: 100%;
        padding: 3px 0.5em;
        text-decoration: none;
        color: MidnightBlue;
        background-color: White;
        border: 2px solid MidnightBlue;
        border-style: inset;
        border-color: White;
      }
    </STYLE>

    <STYLE type="text/css">
      div.div_RootBody {
	position: relative;
	border:2px solid White;
	border-radius:7px;
	background: WhiteSmoke;
	font: normal 14px Arial;
	font-family:Arial, Helvetica, sans-serif;
	color:Black;
	padding: 0px 1em;
	text-align:left;
      }
    </STYLE>

		
   
		
		
   
    
	      <script type="text/javascript">
	      //var dataSet1 = ;
          //drawHorizontalBarChart("Bars1", dataSet1, ".chart", 600, 1400);
      </script>
	  </div>
	  <div class="columnB pull-right">
			
		</div>
		</div>
		 </div>
      <!-- Example row of columns -->
      <div class="row">
        <div class="span6">
          <h2>Games Missed by Player</h2>
          <p>Profile of games missed by player highlights individual issues.</p>
          <div class="chart2"></div>
		  <script type="text/javascript">
		 
		  var leagueGamesMissed = <?php echo($leagueGamesMissedByPlayer); ?>;
		   
          drawHorizontalBarChart("Bars2", leagueGamesMissed, ".chart2", 150, 600);
      </script>
        </div>
        <div class="span6">
          <h2>Player Injuries Length Histogram</h2>
          <p>Details chronic minor issues or long term serious conditions.</p>
          <div class="chart3"></div>
		  <script type="text/javascript">
		  var numInjuriesByGames = <?php echo($numInjuriesByGamesMissed); ?>;
        drawHorizontalBarChart("Bars3", numInjuriesByGames, ".chart3", 125, 600);
      </script>
       </div>
      </div>

      <hr>

      <footer>
        <p>&copy; Team Submarino 2013 -- fueled by Sports Data LLC</p>
      </footer>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="asset/js/jquery-1.9.0.js"></script>
    <script src="asset/js/bootstrap-transition.js"></script>
    <script src="asset/js/bootstrap-alert.js"></script>
    <script src="asset/js/bootstrap-modal.js"></script>
    <script src="asset/js/bootstrap-dropdown.js"></script>
    <script src="asset/js/bootstrap-scrollspy.js"></script>
    <script src="asset/js/bootstrap-tab.js"></script>
    <script src="asset/js/bootstrap-tooltip.js"></script>
    <script src="asset/js/bootstrap-popover.js"></script>
    <script src="asset/js/bootstrap-button.js"></script>
    <script src="asset/js/bootstrap-collapse.js"></script>
    <script src="asset/js/bootstrap-carousel.js"></script>
    <script src="asset/js/bootstrap-typeahead.js"></script>
	
	

  </body>
</html>
    
    <?php
    /*
    // injury category names: ????
    $leagueDao = new LeagueDao();
    //$results = $leagueDao->getInjuryReoccurencesByTeam();
    //print_r($results);

    $results1 = $leagueDao->getGamesMissedByTeam();
    print_r($results1 );
    echo('<br/>');
    
    $results2 = $leagueDao->getAvgLengthOfInjuryByTeam();
    print_r($results2 );
    echo('<br/>');
    
    $results3 = $leagueDao->getGamesMissedByTeam("muscle");
    print_r($results3 );
    echo('<br/>');
    
    $results4 = $leagueDao->getAvgLengthOfInjuryByTeam("muscle");
    print_r($results4 );
    echo('<br/>');
    
    $results5 = $leagueDao->getInjuryReoccurencesByTeam();
    print_r($results5 );
    echo('<br/>');
    
    $results6 = $leagueDao->getInjuryReoccurencesByTeam("muscle");
    print_r($results6 );
    echo('<br/>');
    
    $teamDao = new TeamDao();
    $results7 = $teamDao->getGamesMissedByPlayer("660");
    print_r($results7);
    echo('<br/>');
    $results8 = $teamDao->getGamesMissedByPlayer("660","muscle");
    print_r($results8);
    echo('<br/>');
    */
	?>
