<div class="bubbleInfo event [[+fullday:eq=`1`:then=`event-fullday`:else=``]]">
  <span class="info" data-toggle="modal" href="#modal-[[+id]]">[[+fullday:eq=`1`:then=``:else=`[[+startTime]] - `]][[+subject]]</span>
  <div class="modal hide fade" id="modal-[[+id]]">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"></button>
    <h3>[[+subject]]</h3>
  </div>
  <div class="modal-body">
    [[+importance:notempty=`<p><span class="label label-important">Important</span></p>`:default=``]]
    <p>[[+startTime]] [[+endTime:notempty=`- [[+endTime]]`]]</p>
    <p>Start: [[+start:strtotime:date=`%d.%m.%Y %H:%M`]]<br/>
       Ende: [[+end:strtotime:date=`%d.%m.%Y %H:%M`]]<br/>
       Ort: [[+location]]<br/>
    </p>
    <p>[[+detail:stripTags:strip]]</p>
  </div>
  <div class="modal-footer">
    <a href="[[~25? &uid=`[[+uid]]`]]" class="btn btn-primary">EDIT</a>
    <a href="#" class="btn" data-dismiss="modal">Close</a>
  </div>
</div>