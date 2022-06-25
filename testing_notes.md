# Notes for manual testing of major functionality

Ideally someday these will be folded into automated testing routines, until
then, we need to be able to enumerate and manually test the core functionality.

## Public Access

### Users can
- front page renders listing
- column headers sort
- can view shared agreements

### Users cannot
- access committees
- access minutes
- search

## Member users can
- View Recently Active Items (30 days)
- All Agreements listing
- All Minutes listing
- View filtered listing based on committee name
- View minutes
- View non-shared agreements
  * View diff [example](http://gocoho.org/boa/?id=previous_version&agr_id=6&prev_id=1)
  * View minutes from previous 50 days (same example as above)
- Simple search [example](http://gocoho.org/boa/index.php?id=search&q=fence)
- Advanved search [example](http://gocoho.org/boa/index.php?id=search&q=fence&cmty=0&startday=1&startmonth=1&startyear=2001&endday=31&endmonth=12&endyear=2001&show_docs=agreements)

## Admin users can
- login
- agreements:
  * create new
  * view the new entry
  * edit the new entry
  * save changes to the new entry
  * view the changes
  * delete the test entry
- minutes:
  * create new
  * view the new entry
  * edit the new entry
  * save changes to the new entry
  * view the changes
  * delete the test entry

