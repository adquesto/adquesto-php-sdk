# Adquesto JavaScript Plugin

## Events

The Adquesto plugin emits events when it changes its state. Events can be used for integration of frontend actions.

### Available events

* `adquesto.ready` - triggered when the plugin is loaded and ready
* `adquesto.emissionskip` - triggered when there is no adquest to display or reader has an active subscription (the page content is available without showing an adquest)
* `adquesto.emissionstart` - triggered after the page content becomes blurred (before displaying adquest)
* `adquesto.emissionend` - triggered after the page blur is removed and page content is unveiled (after correct answer)

### JavaScript events handling example

```javascript
document.addEventListener('adquesto.ready', function(event) {
  console.log('adquesto.ready', event);
}, true);
document.addEventListener('adquesto.emissionstart', function(event) {
  console.log('adquesto.emissionstart', event);
}, true);
document.addEventListener('adquesto.emissionend', function(event) {
  console.log('adquesto.emissionend', event);
}, true);
document.addEventListener('adquesto.emissionskip', function(event) {
  console.log('adquesto.emissionskip', event);
}, true);
```
