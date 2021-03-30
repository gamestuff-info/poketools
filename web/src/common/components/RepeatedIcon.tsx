import {FontAwesomeIcon, FontAwesomeIconProps} from '@fortawesome/react-fontawesome';

interface RepeatedIconProps extends FontAwesomeIconProps {
    count: number
}

export default function RepeatedIcon(props: RepeatedIconProps) {
    const icons = [];
    for (let i = 0; i < props.count; ++i) {
        icons.push(<FontAwesomeIcon key={i} {...props}/>);
    }

    return (<span>{icons}</span>);
}
