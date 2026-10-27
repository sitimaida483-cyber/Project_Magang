import { FaceDetector, FilesetResolver } from '@mediapipe/tasks-vision';

let faceDetectorPromise;

window.createFaceDetector = function () {
    if (!faceDetectorPromise) {
        faceDetectorPromise = (async () => {
            const vision = await FilesetResolver.forVisionTasks(
                'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@1.1.1/wasm',
            );

            return FaceDetector.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath: '/models/blaze_face_short_range.tflite',
                    delegate: 'CPU',
                },
                runningMode: 'IMAGE',
                minDetectionConfidence: 0.6,
            });
        })();
    }

    return faceDetectorPromise;
};
