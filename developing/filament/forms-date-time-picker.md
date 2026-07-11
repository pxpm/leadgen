# Date-Time Picker (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/forms/date-time-picker
`DateTimePicker::make("published_at")->displayFormat("d/m/Y")->seconds(false)->timezone("Europe/London")->minDate(now())->maxDate(now()->addYear())->native(false)->weekStartsOnMonday()->disabledDates([now()->addDay()])->withoutTime()` for date only.
