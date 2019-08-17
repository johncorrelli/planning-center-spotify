# planning-center-spotify

I'll add more thorough documentation. However for quick reference:

The goal of this is to take the "Songs" in your Planning Center Service schedule to Spotify playlists.
This takes about 10 minutes for initial setup, after that things run smoothly without much needed.

## Step 1
- Clone the repo to your local machine.
- Run `composer install`

## Step 2: API Authentication
- Use the "Songs" feature within Planning Center
- Each Song must be linked to a Spotify Song (this is easy to do within Planning Center)
- Service Items for songs must be linked to the a Song.
- You'll then need to setup a Planning Center [developer account](https://developer.planning.center/docs/#/introduction).
  - Create a new **Personal Access Token**
  - Note your `Application Id` and `Secret`
- Then head over to Spotify and create an [application](https://developer.spotify.com/documentation/general/guides/app-settings/#register-your-app)
  - Create a new application
  - You are not creating a commercial integration
  - Note your `Client ID` and `Client Secret`

## Step 3: Running the script

Running the script is as simple as running `./sync` from the root directory of the repo.

### For the first time:

Running this for the first time will take a few extra seconds of setup, as we'll need to enter the four values we generated in Step 2.

Those values are:
  - Planning Center:
    - `Application Id`
    - `Secret Key`
  - Spotify
    - `Client ID`
    - `Client Secret`

Once you've entered these values, they'll be stored in a git ignored file at `storage/auth.json` and you won't need to enter them again.

## Step 4 (optional): Automation

Once you've setup authorization, you're good to go.
You can set this up to run on a crontab, allowing it to automatically update your Spotify playlists without the need of your interaction.
