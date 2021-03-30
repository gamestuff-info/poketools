import {useState} from 'react';
import {Button, Modal} from 'react-bootstrap';
import PktMarkdown from '../common/components/PktMarkdown';

export default function EncounterNote(props: { encounter: ApiRecord.Pokemon.Encounter }) {
    const {encounter} = props;
    const [shown, setShown] = useState(false);
    if (!encounter.note) {
        return null;
    }

    return (
        <>
            <Button variant="outline-info"
                    size="sm"
                    onClick={() => setShown(!shown)}
            >
                Note
            </Button>
            <Modal show={shown} onHide={() => setShown(false)}>
                <Modal.Header closeButton>
                    <Modal.Title>Note</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <PktMarkdown>{encounter.note}</PktMarkdown>
                </Modal.Body>
            </Modal>
        </>
    );
}
