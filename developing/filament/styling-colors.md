# Styling Colors (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/styling/colors
### Available Colors: primary secondary success warning danger info gray neutral
### Register Custom
```php
use Filament\Support\Colors\Color;
Color::register(['custom'=>Color::hex('#ff5733')]);
```
### Use in Panel
`->colors(['primary'=>Color::Amber,'danger'=>Color::Rose,'custom'=>Color::hex('#ff5733')])`
### Tailwind Integration
Custom colors automatically added to Tailwind config. Available as `bg-custom-500 text-custom-600` etc.
### Color Manager
`Color::Amber` `Color::Blue` `Color::Cyan` `Color::Emerald` `Color::Fuchsia` `Color::Gray` `Color::Green` etc.
