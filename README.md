# seamlessopen

2 Main components - Backend and App

Backend:
- crawl script to get
  - coordinates
  - address
  - realtor name
- crawl agent homepage to get number
  - no number no active listing
- bootstrap themed personal page for each open house signins
  - possibly a login system here
- API ends for data to App
 
 App:
 - splash screen: get started button with form to fill out, store profile
 - If address is not within NJ, display NJ only 
 - logged in: edit profile, view history or start open housing
  - edit profile
  - view history: list of attended open houses
  - start open houses: blank with a stop button
   - if in an open house, show current status with custom signin phone number to text to a different number other than the crawled one
