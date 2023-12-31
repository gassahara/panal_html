/* global $, JitsiMeetJS */

const options = {
    hosts: {
        anonymousdomain: "meet.jitsi", // Internal domain. meet.jitsi by default (docker). may be used to infer others. 
        muc: "muc.meet.jitsi", // Session coordinator. If this is wrong, the connection fails with Strophe: BOSH-Connection failed: improper-addressing
        focus: "focus.meet.jitsi", // Video stream coordinator. If this is wrong, you won't see any video and get "Focus error"s on the console. 
    },
    bosh: 'https://curare2019.ddns.net:8444/http-bind',
};

const confOptions = {
};

let connection = null;
let isJoined = false;
let room = null;

let localTracks = [];
const remoteTracks = {};

/**
 * Handles local tracks.
 * @param tracks Array with JitsiTrack objects
 */
async function onLocalTracks(tracks) {
    localTracks = tracks;
    for (let i = 0; i < localTracks.length; i++) {
        localTracks[i].addEventListener(
            JitsiMeetJS.events.track.TRACK_AUDIO_LEVEL_CHANGED,
            audioLevel => console.warn(`Audio Level local: ${audioLevel}`));
        localTracks[i].addEventListener(
            JitsiMeetJS.events.track.TRACK_MUTE_CHANGED,
            () => console.warn('local track muted'));
        localTracks[i].addEventListener(
            JitsiMeetJS.events.track.LOCAL_TRACK_STOPPED,
            () => console.warn('local track stoped'));
        localTracks[i].addEventListener(
            JitsiMeetJS.events.track.TRACK_AUDIO_OUTPUT_CHANGED,
            deviceId =>
            console.warn(
                `track audio output device was changed to ${deviceId}`));
        if (localTracks[i].getType() === 'video') {

            $('body').append(`<video autoplay='1' id='localVideo${i}' />`);
            localTracks[i].attach($(`#localVideo${i}`)[0]);
        } else {
            $('body').append(
                `<audio autoplay='1' muted='true' id='localAudio${i}' />`);
            localTracks[i].attach($(`#localAudio${i}`)[0]);
        }
	console.warn("roomba", {isJoined});
        if (isJoined) {
	    console.warn("roomba adding local to conf", localTracks[i]);
            room.addTrack(localTracks[i]);
        }
    }
}

/**
 * Handles remote tracks
 * @param track JitsiTrack object
 */
function onRemoteTrack(track) {
    console.warn("roomba TRACK NEW", {track});
    if (track.isLocal()) {
	console.warn("roomba LOCAL TRACK ", track.getType(), {track});	
        return;
    }
    console.warn("roomba TRACK ADDED", {track});
    console.warn("roomba TRACK DETAILS", track.getType());
    const participant = track.getParticipantId();

    console.warn("roomba TRACK FOR PARTICIPANT", {participant});
    if (!remoteTracks[participant] || remoteTracks[participant]===undefined) {
        remoteTracks[participant] = [];
    }
    const idx = remoteTracks[participant].push(track);
    console.warn("roomba TRACK INDEX", {remoteTracks});
    
    track.addEventListener(
        JitsiMeetJS.events.track.TRACK_AUDIO_LEVEL_CHANGED,
        audioLevel => console.warn(`Audio Level remote: ${audioLevel}`));
    track.addEventListener(
        JitsiMeetJS.events.track.TRACK_MUTE_CHANGED,
        () => console.warn('remote track muted'));
    track.addEventListener(
        JitsiMeetJS.events.track.LOCAL_TRACK_STOPPED,
        () => console.warn('remote track stoped'));
    track.addEventListener(JitsiMeetJS.events.track.TRACK_AUDIO_OUTPUT_CHANGED,
        deviceId =>
            console.warn(
                `track audio output device was changed to ${deviceId}`));
    const id = participant + track.getType() + idx;

    if (track.getType() === 'video') {
        $('body').append(
            `<video autoplay='1' id='${participant}video${idx}' />`);
    } else {
        $('body').append(
            `<audio autoplay='1' id='${participant}audio${idx}' />`);
    }
    console.warn("roomba TRACK ATACHING");
    track.attach($(`#${id}`)[0]);
}

/**
 * That function is executed when the conference is joined
 */
async function onConferenceJoined() {
    console.warn('roomba conference joined!', {localTracks});
    isJoined = true;
    for (let i = 0; i < localTracks.length; i++) {
	console.warn("roomba adding local tracks", localTracks[i]);
        await room.addTrack(localTracks[i]);
	console.warn("roomba added track");
    }
    console.warn("roomba room", {room}, room.getLocalTracks());

}

/**
 *
 * @param id
 */
function onUserLeft(id) {
    console.warn('user left');
    if (!remoteTracks[id]) {
        return;
    }
    const tracks = remoteTracks[id];

    for (let i = 0; i < tracks.length; i++) {
        tracks[i].detach($(`#${id}${tracks[i].getType()}`));
    }
}

/**
 * That function is called when connection is established successfully
 */
function onConnectionSuccess() {
    JitsiMeetJS.createLocalTracks({ devices: [ 'audio', 'video' ] })
	.then(onLocalTracks)
	.catch(error => {
            throw error;
	});

    if (JitsiMeetJS.mediaDevices.isDeviceChangeAvailable('output')) {
	JitsiMeetJS.mediaDevices.enumerateDevices(devices => {
            const audioOutputDevices
		  = devices.filter(d => d.kind === 'audiooutput');

            if (audioOutputDevices.length > 1) {
		$('#audioOutputSelect').html(
                    audioOutputDevices
			.map(
                            d =>
                            `<option value="${d.deviceId}">${d.label}</option>`)
			.join('\n'));

		$('#audioOutputSelectWrapper').show();
            }
	});
    }

    room = connection.initJitsiConference('roomtres', confOptions);
    console.warn("roomba init", {room});
    room.on(JitsiMeetJS.events.conference.TRACK_ADDED, onRemoteTrack);
    room.on(JitsiMeetJS.events.conference.TRACK_REMOVED, track => {
        console.warn("roomba", `track removed!!!${track}`);
    });
    room.on(
        JitsiMeetJS.events.conference.CONFERENCE_JOINED,
        onConferenceJoined);
    room.on(JitsiMeetJS.events.conference.USER_JOINED, id => {
        console.warn("roomba", 'user join');
        remoteTracks[id] = [];
    });
    room.on(JitsiMeetJS.events.conference.USER_LEFT, onUserLeft);
    room.on(JitsiMeetJS.events.conference.TRACK_MUTE_CHANGED, track => {
        console.warn("roomba", `${track.getType()} - ${track.isMuted()}`);
    });
    room.on(
        JitsiMeetJS.events.conference.DISPLAY_NAME_CHANGED,
        (userID, displayName) => console.warn("roomba", `${userID} - ${displayName}`));
    room.on(
        JitsiMeetJS.events.conference.TRACK_AUDIO_LEVEL_CHANGED,
        (userID, audioLevel) => console.warn("roomba", `${userID} - ${audioLevel}`));
    room.on(
        JitsiMeetJS.events.conference.PHONE_NUMBER_CHANGED,
        () => console.warn("roomba", `${room.getPhoneNumber()} - ${room.getPhonePin()}`));
    room.join();
}

/**
 * This function is called when the connection fail.
 */
function onConnectionFailed(e) {
    console.warn({connection});
    console.warn({e});
    alert('Failed');
    console.error('Connection Failed!');
}

/**
 * This function is called when the connection fail.
 */
function onDeviceListChanged(devices) {
    console.info('current devices', devices);
}

/**
 * This function is called when we disconnect.
 */
function disconnect() {
    console.warn('disconnect!');
    connection.removeEventListener(
        JitsiMeetJS.events.connection.CONNECTION_ESTABLISHED,
        onConnectionSuccess);
    connection.removeEventListener(
        JitsiMeetJS.events.connection.CONNECTION_FAILED,
        onConnectionFailed);
    connection.removeEventListener(
        JitsiMeetJS.events.connection.CONNECTION_DISCONNECTED,
        disconnect);
}

/**
 *
 */
function unload() {
    for (let i = 0; i < localTracks.length; i++) {
        localTracks[i].dispose();
    }
    room.leave();
    connection.disconnect();
}

let isVideo = true;

/**
 *
 */
function switchVideo() { // eslint-disable-line no-unused-vars
    isVideo = !isVideo;
    if (localTracks[1]) {
        localTracks[1].dispose();
        localTracks.pop();
    }
    JitsiMeetJS.createLocalTracks({
        devices: [ isVideo ? 'video' : 'desktop' ]
    })
        .then(tracks => {
            localTracks.push(tracks[0]);
            localTracks[1].addEventListener(
                JitsiMeetJS.events.track.TRACK_MUTE_CHANGED,
                () => console.warn('local track muted'));
            localTracks[1].addEventListener(
                JitsiMeetJS.events.track.LOCAL_TRACK_STOPPED,
                () => console.warn('local track stoped'));
            localTracks[1].attach($('#localVideo1')[0]);
            room.addTrack(localTracks[1]);
        })
        .catch(error => console.warn(error));
}

/**
 *
 * @param selected
 */
function changeAudioOutput(selected) { // eslint-disable-line no-unused-vars
    JitsiMeetJS.mediaDevices.setAudioOutputDevice(selected.value);
}

$(window).bind('beforeunload', unload);
$(window).bind('unload', unload);

// JitsiMeetJS.setLogLevel(JitsiMeetJS.logLevels.ERROR);
const initOptions = {
    enableWindowOnErrorHandler: true,
    disableRtx: true,   
    disableAudioLevels: true
};

JitsiMeetJS.init(initOptions);
connection = new JitsiMeetJS.JitsiConnection(null, null, options);
console.warn("________________", {connection});

connection.addEventListener(
    JitsiMeetJS.events.connection.CONNECTION_ESTABLISHED,
    onConnectionSuccess);
connection.addEventListener(
    JitsiMeetJS.events.connection.CONNECTION_FAILED,
    onConnectionFailed);
connection.addEventListener(
    JitsiMeetJS.events.connection.CONNECTION_DISCONNECTED,
    disconnect);

JitsiMeetJS.mediaDevices.addEventListener(
    JitsiMeetJS.events.mediaDevices.DEVICE_LIST_CHANGED,
    onDeviceListChanged);

connection.connect();

