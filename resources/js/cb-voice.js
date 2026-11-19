// Głosowe CB Kanał 19 – krótkofalówka (naciśnij i mów) przez serwer LiveKit
import { Room, RoomEvent, Track } from 'livekit-client';

let room = null;

export async function connect(url, token, { onSpeakers, onState } = {}) {
    await disconnect();
    room = new Room({ adaptiveStream: true, dynacast: true });
    room.on(RoomEvent.TrackSubscribed, (track) => {
        if (track.kind === Track.Kind.Audio) {
            const el = track.attach();
            el.dataset.k19Voice = '1';
            document.body.appendChild(el);
        }
    });
    room.on(RoomEvent.TrackUnsubscribed, (track) => track.detach().forEach((el) => el.remove()));
    room.on(RoomEvent.ActiveSpeakersChanged, (speakers) => onSpeakers?.(speakers.map((p) => p.name || p.identity)));
    room.on(RoomEvent.ParticipantConnected, () => onState?.(room.remoteParticipants.size + 1));
    room.on(RoomEvent.ParticipantDisconnected, () => onState?.(room.remoteParticipants.size + 1));
    room.on(RoomEvent.Disconnected, () => onState?.(null));
    await room.connect(url, token);
    await room.startAudio();
    onState?.(room.remoteParticipants.size + 1);
    return room;
}

export async function talk(on) {
    if (!room) return false;
    await room.localParticipant.setMicrophoneEnabled(on);
    return true;
}

export async function disconnect() {
    if (room) {
        await room.disconnect();
        room = null;
    }
    document.querySelectorAll('[data-k19-voice]').forEach((el) => el.remove());
}

window.K19Voice = { connect, talk, disconnect };
