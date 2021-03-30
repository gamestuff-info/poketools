import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {faCircleNotch} from '@fortawesome/free-solid-svg-icons';
import {Alert} from 'react-bootstrap';

interface LoadingProps {
    uncontained?: boolean
}

function Throbber(props: {}) {
    return (
        <span>
            <FontAwesomeIcon icon={faCircleNotch} spin/>
            &nbsp;
            Loading...
        </span>
    );
}

export default function Loading(props: Partial<LoadingProps>) {
    if (!(props.uncontained ?? false)) {
        return (
            <div>
                <Alert variant="secondary"><Throbber/></Alert>
            </div>
        );
    } else {
        return (<Throbber/>);
    }
}
