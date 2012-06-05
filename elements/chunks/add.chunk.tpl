<div class="alert alert-[[+type:notempty=`success`:default=`error`]]">[[+errors]]</div>
<form action="[[~[[*id]]]]" method="post" enctype="multipart/form-data">
  <input type="hidden" name="uid" value="[[+editable]]" />
  <input type="hidden" name="change_key" value="[[+change_key]]" />
  <label for="subject">Subject</label>
  <input id="subject" type="text" name="subject" value="[[+subject:default=``]]" /><br/>
  <label for="location">Location</label>
  <input id="location" type="text" name="location" value="[[+location:default=``]]"/><br/>
  <label for="start">Start</label>
  <input id="start" type="date" name="start[date]" class="datepicker" value="[[+start_date:strtotime:date=`%m/%d/%Y`:default=``]]"/>
  <select name="start[hour]">[[+hours]]</select>
  <select name="start[minute]">[[+minutes]]</select><br/>
  <label for="end">End</label>
  <input id="end" type="date" name="end[date]" class="datepicker" value="[[+end_date:strtotime:date=`%m/%d/%Y`:default=``]]"/>
  <select name="end[hour]">[[+hours]]</select>
  <select name="end[minute]">[[+minutes]]</select><br/>
  <label for="importance-low">[[%ews.importance.low]]
    <input id="importance-low" type="radio" name="importance" value="Low" [[+importance:eq=`Low`:then=`checked`:else=``]]/>
  </label>
  <label for="importance-high">[[%ews.importance.high]]
    <input id="importance-high" type="radio" name="importance" value="High" [[+importance:eq=`High`:then=`checked`:else=``]]/>
  </label><br/>
  <label for="fullday">Fullday</label>
  <input id="fullday" type="checkbox" name="allday" /><br/>
  <label for="body">Body</label>
  <textarea id="body" name="body" cols="200" rows="5">[[+body:default=``]]</textarea><br/>
  <label for="file">Attachment</label>
  <input id="file" type="file" name="file" /><br/>
  <label for="email">E-Mail</label>
  <input id="email" type="email" name="email" value="[[+email:default=``]]"/><br/>
  <input class="btn btn-success" type="submit" name="edit" value="[[+submit:default=`Erstellen`]]" />
  [[+cancelable:notempty=`<input class="btn btn-warning" type="submit" name="cancel" value="[[%ews.cancel]]" />`:default=``]]
</form>