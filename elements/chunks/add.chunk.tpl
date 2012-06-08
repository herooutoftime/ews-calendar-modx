<div class="alert alert-[[+type:notempty=`success`:default=`error`]]">[[+errors]]</div>
<form action="[[~[[*id]]]]" method="post" enctype="multipart/form-data">
  <input type="hidden" name="uid" value="[[+editable]]" />
  <input type="hidden" name="change_key" value="[[+change_key]]" />
  <label for="subject">[[%ews.form.subject]]</label>
  <input id="subject" type="text" name="subject" value="[[+subject:default=``]]" /><br/>
  <label for="location">[[%ews.form.location]]</label>
  <input id="location" type="text" name="location" value="[[+location:default=``]]"/><br/>
  <label for="start">[[%ews.form.start]]</label>
  <input id="start" type="date" name="start[date]" class="datepicker" value="[[+start_date:strtotime:date=`%m/%d/%Y`:default=``]]"/>
  <select name="start[hour]">[[+hours]]</select>
  <select name="start[minute]">[[+minutes]]</select><br/>
  <label for="end">[[%ews.form.end]]</label>
  <input id="end" type="date" name="end[date]" class="datepicker" value="[[+end_date:strtotime:date=`%m/%d/%Y`:default=``]]"/>
  <select name="end[hour]">[[+hours]]</select>
  <select name="end[minute]">[[+minutes]]</select><br/>
  <label for="importance-low">[[%ews.form.importance.low]]
    <input id="importance-low" type="radio" name="importance" value="Low" [[+importance:eq=`Low`:then=`checked`:else=``]]/>
  </label>
  <label for="importance-high">[[%ews.form.importance.high]]
    <input id="importance-high" type="radio" name="importance" value="High" [[+importance:eq=`High`:then=`checked`:else=``]]/>
  </label><br/>
  <label for="fullday">[[%ews.form.allday]]</label>
  <input id="fullday" type="checkbox" name="allday" /><br/>
  <label for="body">[[%ews.form.body]]</label>
  <textarea id="body" name="body" cols="200" rows="5">[[+body:default=``]]</textarea><br/>
  <label for="file">[[%ews.form.attachments.exist]]</label>
  <ul class="thumbnails">[[+attachments]]</ul>
  <label for="file">[[%ews.form.attachments]]</label>
  <input id="file" type="file" name="file" /><br/>
  <label for="email">[[%ews.form.sendto]]</label>
  <input id="email" type="email" name="email" value="[[+email:default=``]]"/><br/>
  <input class="btn btn-success" type="submit" name="edit" value="[[+submit:default=`Erstellen`]]" />
  [[+cancelable:notempty=`<input class="btn btn-warning" type="submit" name="cancel" value="[[%ews.form.event.cancel]]" />`:default=``]]
  [[+deleteable:notempty=`<input class="btn btn-danger" type="submit" name="delete" value="[[%ews.form.event.delete]]" />`:default=``]]
</form>