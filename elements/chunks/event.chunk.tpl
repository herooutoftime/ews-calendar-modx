<div class="bubbleInfo event [[+fullday:eq=`1`:then=`event-fullday`:else=``]]">
  <span class="info">[[+fullday:eq=`1`:then=``:else=`[[+startTime]] - `]][[+subject]]</span>
  <div class="popup">
    <h4>[[+subject]]</h4>
    [[+startTime]] [[+endTime:notempty=`- [[+endTime]]`]]
    <p>Start: [[+start:strtotime:date=`%d.%m.%Y %H:%M`]]<br/>
       Ende: [[+end:strtotime:date=`%d.%m.%Y %H:%M`]]<br/>
       Ort: [[+location]]<br/>
    </p>
    <p><a href="[[~25? &uid=`[[+uid]]`]]">EDIT</a></p>
    <p>[[+detail:stripTags:strip]]</p>
  </div>
</div>