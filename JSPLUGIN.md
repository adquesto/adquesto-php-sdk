# Adquesto JavaScript Plugin

## Events

The Adquesto plugin emits events when it changes its state. Events can be used for integration of frontend actions.

### Available events

* `adquesto.ready` - triggered when the plugin is loaded and ready
* `adquesto.emissionskip` - triggered when there is no adquest to display or reader has an active subscription (the page content is available without showing an adquest)
* `adquesto.emissionstart` - triggered after the page content becomes blurred (before displaying adquest)
* `adquesto.emissionend` - triggered after the page blur is removed and page content is unveiled (after correct answer)
* `adquesto.adblockdetected` - triggered when adblock has been detected (and quest is not presented)
* `adquesto.subscriptiontoggle` - triggered when user clicked on link providing to subscription view or is returning back from subscription to quest view (state in event.detail)
* `adquesto.pass` - triggered when quest was not appear for some reason (reason in event.detail)

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

document.addEventListener('adquesto.adblockdetected', function(event) {
  console.log('adquesto.adblockdetected', event);
}, true);

document.addEventListener('adquesto.subscriptiontoggle', function(event) {
  console.log('adquesto.subscriptiontoggle', event.detail, event);
}, true);

document.addEventListener('adquesto.pass', function(event) {
  console.log('adquesto.pass', event.detail, event);
}, true);
```
