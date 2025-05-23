<?php
// تنظیم هدر خروجی به UTF-8
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP Example - CodePen</title>
  <style>
    @import url(https://fonts.googleapis.com/css?family=Ubuntu:400,700);
    * {
      -webkit-box-sizing: border-box;
      -moz-box-sizing: border-box;
      box-sizing: border-box;  
    }
    body {
      background: #1f253d;
    }
    ul {
      list-style-type: none;
      margin: 0;
      padding-left: 0;
    }
    h1 {
      font-size: 23px;
    }
    h2 {
      font-size: 17px;
    }
    p {
      font-size: 15px;
    }
    a {
      text-decoration: none;
      font-size: 15px;
    }
    a:hover {
      text-decoration: underline;
    }
    h1, h2, p, a, span{
      color: #fff;
    }
    .scnd-font-color {
      color: #9099b7;
    }
    .titular {
      display: block;
      line-height: 60px;
      margin: 0;
      text-align: center;
      border-top-left-radius: 5px;
      border-top-right-radius: 5px;
    }
    .horizontal-list {
      margin: 0;
      padding: 0;
      list-style-type: none;
    }
    .horizontal-list li {
      float: left;
    }
    .block {
      margin: 25px 25px 0 0;
      background: #394264;
      border-radius: 5px;
      float: left;
      width: 300px;
      overflow: hidden;
    }
    /******************************************** LEFT CONTAINER *****************************************/
    .left-container {}
    .menu-box {
      height: 360px;
    }
    .donut-chart-block {
      overflow: hidden;
    }
    .donut-chart-block .titular {
      padding: 10px 0;
    }
    .os-percentages li {
      width: 75px;
      border-left: 1px solid #394264;
      text-align: center;					
      background: #50597b;
    }
    .os {
      margin: 0;
      padding: 10px 0 5px;
      font-size: 15px;		
    }
    .os.ios {
      border-top: 4px solid #e64c65;
    }
    .os.mac {
      border-top: 4px solid #11a8ab;
    }
    .os.linux {
      border-top: 4px solid #fcb150;
    }
    .os.win {
      border-top: 4px solid #4fc4f6;
    }
    .os-percentage {
      margin: 0;
      padding: 0 0 15px 10px;
      font-size: 25px;
    }
    .line-chart-block, .bar-chart-block {
      height: 400px;
    }
    .line-chart {
      height: 200px;
      background: #11a8ab;
    }
    .time-lenght {
      padding-top: 22px;
      padding-left: 38px;
      overflow: hidden;
    }
    .time-lenght-btn {
      display: block;
      width: 70px;
      line-height: 32px;
      background: #50597b;
      border-radius: 5px;
      font-size: 14px;
      text-align: center;
      margin-right: 5px;
      -webkit-transition: background .3s;
      transition: background .3s;
    }
    .time-lenght-btn:hover {
      text-decoration: none;
      background: #e64c65;
    }
    .month-data {
      padding-top: 28px;
    }
    .month-data p {
      display: inline-block;
      margin: 0;
      padding: 0 25px 15px;            
      font-size: 16px;
    }
    .month-data p:last-child {
      padding: 0 25px;
      float: right;
      font-size: 15px;
    }
    .increment {
      color: #e64c65;
    }
    /******************************************
    ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓
    ESTILOS PROPIOS DE LOS GRÄFICOS
    ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ ↑ 
    GRAFICO LINEAL
    ******************************************/
    .grafico {
      padding: 2rem 1rem 1rem;
      width: 100%;
      height: 100%;
      position: relative;
      color: #fff;
      font-size: 80%;
    }
    .grafico span {
      display: block;
      position: absolute;
      bottom: 3rem;
      left: 2rem;
      height: 0;
      border-top: 2px solid;
      transform-origin: left center;
    }
    .grafico span > span {
      left: 100%; bottom: 0;
    }
    [data-valor='25'] {width: 75px; transform: rotate(-45deg);}
    [data-valor='8'] {width: 24px; transform: rotate(65deg);}
    [data-valor='13'] {width: 39px; transform: rotate(-45deg);}
    [data-valor='5'] {width: 15px; transform: rotate(50deg);}
    [data-valor='23'] {width: 69px; transform: rotate(-70deg);}
    [data-valor='12'] {width: 36px; transform: rotate(75deg);}
    [data-valor='15'] {width: 45px; transform: rotate(-45deg);}
    [data-valor]:before {
      content: '';
      position: absolute;
      display: block;
      right: -4px;
      bottom: -3px;
      padding: 4px;
      background: #fff;
      border-radius: 50%;
    }
    [data-valor='23']:after {
      content: '+' attr(data-valor) '%';
      position: absolute;
      right: -2.7rem;
      top: -1.7rem;
      padding: .3rem .5rem;
      background: #50597B;
      border-radius: .5rem;
      transform: rotate(45deg);  
    }
    [class^='eje-'] {
      position: absolute;
      left: 0;
      bottom: 0rem;
      width: 100%;
      padding: 1rem 1rem 0 2rem;
      height: 80%;
    }
    .eje-x {
      height: 2.5rem;
    }
    .eje-y li {
      height: 25%;
      border-top: 1px solid #777;
    }
    [data-ejeY]:before {
      content: attr(data-ejeY);
      display: inline-block;
      width: 2rem;
      text-align: right;
      line-height: 0;
      position: relative;
      left: -2.5rem;
      top: -.5rem;
    } 
    .eje-x li {
      width: 33%;
      float: left;
      text-align: center;
    }
    /******************************************
    GRAFICO CIRCULAR PIE CHART
    ******************************************/
    .donut-chart {
      position: relative;
      width: 200px;
      height: 200px;
      margin: 0 auto 2rem;
      border-radius: 100%;
    }
    p.center-date {
      background: #394264;
      position: absolute;
      text-align: center;
      font-size: 28px;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      width: 130px;
      height: 130px;
      margin: auto;
      border-radius: 50%;
      line-height: 35px;
      padding: 15% 0 0;
    }
    .center-date span.scnd-font-color {
      line-height: 0; 
    }
    .recorte {
      border-radius: 50%;
      clip: rect(0px, 200px, 200px, 100px);
      height: 100%;
      position: absolute;
      width: 100%;
    }
    .quesito {
      border-radius: 50%;
      clip: rect(0px, 100px, 200px, 0px);
      height: 100%;
      position: absolute;
      width: 100%;
      font-family: monospace;
      font-size: 1.5rem;
    }
    #porcion1 {
      transform: rotate(0deg);
    }
    #porcion1 .quesito {
      background-color: #E64C65;
      transform: rotate(76deg);
    }
    #porcion2 {
      transform: rotate(76deg);
    }
    #porcion2 .quesito {
      background-color: #11A8AB;
      transform: rotate(140deg);
    }
    #porcion3 {
      transform: rotate(215deg);
    }
    #porcion3 .quesito {
      background-color: #4FC4F6;
      transform: rotate(113deg);
    }
    #porcionFin {
      transform: rotate(-32deg);
    }
    #porcionFin .quesito {
      background-color: #FCB150;
      transform: rotate(32deg);
    }
    .nota-final {
      clear: both;
      color: #4FC4F6;
      font-size: 1rem;
      padding: 2rem 0;
    }
    .nota-final strong {
      color: #E64C65;
    }
    .nota-final a {
      color: #FCB150;
      font-size: inherit;
    }
    /**************************
    BAR-CHART
    **************************/
    .grafico.bar-chart {
      background: #3468AF;
      padding: 0 1rem 2rem 1rem;
      width: 100%;
      height: 60%;
      position: relative;
      color: #fff;
      font-size: 80%;
    }
    .bar-chart [class^='eje-'] {
      padding: 0 1rem 0 2rem;
      bottom: 1rem;
    }
    .bar-chart .eje-x {
      bottom: 0;
    }
    .bar-chart .eje-y li {
      height: 20%;
      border-top: 1px solid #fff;
    }
    .bar-chart .eje-x li {
      width: 14%;
      position: relative;
      text-align: left;
    }
    .bar-chart .eje-x li i {
      transform: rotatez(-45deg) translatex(-1rem);
      transform-origin: 30% 60%;
      display: block;
    }
    .bar-chart .eje-x li:before {
      content: '';
      position: absolute;
      bottom: 1.9rem;
      width: 70%;
      right: 5%;
      box-shadow: 3px 0 rgba(0,0,0,.1), 3px -3px rgba(0,0,0,.1);
    }
    .bar-chart .eje-x li:nth-child(1):before {
      background: #E64C65;  
      height: 570%;
    }
    .bar-chart .eje-x li:nth-child(2):before {
      background: #11A8AB;  
      height: 900%;
    }
    .bar-chart .eje-x li:nth-child(3):before {
      background: #FCB150;  
      height: 400%;
    }
    .bar-chart .eje-x li:nth-child(4):before {
      background: #4FC4F6;  
      height: 290%;
    }
    .bar-chart .eje-x li:nth-child(5):before {
      background: #FFED0D;  
      height: 720%;
    }
    .bar-chart .eje-x li:nth-child(6):before {
      background: #F46FDA;  
      height: 820%;
    }
    .bar-chart .eje-x li:nth-child(7):before {
      background: #15BFCC;  
      height: 520%;
    }
    /*****************************
    USO NÚMEROS MÁGICOS EN ALGUNOS VALORES
    *****************************/
  </style>
</head>
<body>
  <div class="container">
    <!-- DONUT CHART BLOCK (LEFT-CONTAINER) --> 
    <div class="donut-chart-block block"> 
      <h2 class="titular">OS AUDIENCE STATS</h2>
      <div class="donut-chart">
        <!-- PORCIONES GRAFICO CIRCULAR -->
        <div id="porcion1" class="recorte"><div class="quesito ios" data-rel="21"></div></div>
        <div id="porcion2" class="recorte"><div class="quesito mac" data-rel="39"></div></div>
        <div id="porcion3" class="recorte"><div class="quesito win" data-rel="31"></div></div>
        <div id="porcionFin" class="recorte"><div class="quesito linux" data-rel="9"></div></div>
        <!-- FIN AÑADIDO GRÄFICO -->
        <p class="center-date">JUNE<br><span class="scnd-font-color">2013</span></p>        
      </div>
      <ul class="os-percentages horizontal-list">
        <li>
          <p class="ios os scnd-font-color">iOS</p>
          <p class="os-percentage">21<sup>%</sup></p>
        </li>
        <li>
          <p class="mac os scnd-font-color">Mac</p>
          <p class="os-percentage">39<sup>%</sup></p>
        </li>
        <li>
          <p class="linux os scnd-font-color">Linux</p>
          <p class="os-percentage">9<sup>%</sup></p>
        </li>
        <li>
          <p class="win os scnd-font-color">Win</p>
          <p class="os-percentage">31<sup>%</sup></p>
        </li>
      </ul>
    </div>
    <!-- LINE CHART BLOCK (LEFT-CONTAINER) -->
    <div class="line-chart-block block">
      <div class="line-chart">
        <div class='grafico'>
          <ul class='eje-y'>
            <li data-ejeY='30'></li>
            <li data-ejeY='20'></li>
            <li data-ejeY='10'></li>
            <li data-ejeY='0'></li>
          </ul>
          <ul class='eje-x'>
            <li>Apr</li>
            <li>May</li>
            <li>Jun</li>
          </ul>
          <span data-valor='25'>
            <span data-valor='8'>
              <span data-valor='13'>
                <span data-valor='5'>   
                  <span data-valor='23'>   
                    <span data-valor='12'>
                      <span data-valor='15'></span>
                    </span>
                  </span>
                </span>
              </span>
            </span>
          </span>
        </div>
      </div>
      <ul class="time-lenght horizontal-list">
        <li><a class="time-lenght-btn" href="#14">Week</a></li>
        <li><a class="time-lenght-btn" href="#15">Month</a></li>
        <li><a class="time-lenght-btn" href="#16">Year</a></li>
      </ul>
      <ul class="month-data clear">
        <li>
          <p>APR<span class="scnd-font-color"> 2013</span></p>
          <p><span class="entypo-plus increment"> </span>21<sup>%</sup></p>
        </li>
        <li>
          <p>MAY<span class="scnd-font-color"> 2013</span></p>
          <p><span class="entypo-plus increment"> </span>48<sup>%</sup></p>
        </li>
        <li>
          <p>JUN<span class="scnd-font-color"> 2013</span></p>
          <p><span class="entypo-plus increment"> </span>35<sup>%</sup></p>
        </li>
      </ul>
    </div>
    <!-- BAR CHART BLOCK -->
    <div class="bar-chart-block block">
      <h2 class='titular'>By Country <span>*1000</span></h2>
      <div class='grafico bar-chart'>
        <ul class='eje-y'>
          <li data-ejeY='60'></li>
          <li data-ejeY='45'></li>
          <li data-ejeY='30'></li>
          <li data-ejeY='15'></li>
          <li data-ejeY='0'></li>
        </ul>
        <ul class='eje-x'>
          <li data-ejeX='37'><i>España</i></li>
          <li data-ejeX='56'><i>Portugal</i></li>
          <li data-ejeX='25'><i>Italia</i></li>
          <li data-ejeX='18'><i>Grecia</i></li>
          <li data-ejeX='45'><i>EE.UU</i></li>
          <li data-ejeX='50'><i>México</i></li>
          <li data-ejeX='33'><i>Chile</i></li>
        </ul>
      </div>
    </div>
  </div>
  <p class='nota-final'>Este pen es sólo para que <strong>@jlalovi</strong> complemente con los gráficos <a href='https://codepen.io/jlalovi/details/bIyAr'>el suyo</a>.</p>
</body>
</html>