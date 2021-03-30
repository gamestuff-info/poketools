import '../../assets/styles/FlagList.scss';
import PktMarkdown from './PktMarkdown';

interface FlagListProps extends Record<string, any> {
    className?: string
    flags: Array<{ name: string, description: string }>
}

export default function FlagList(props: FlagListProps) {
    const classes = ['pkt-flaglist'];
    if (props.className) {
        classes.push(...props.className.split(' '));
    }

    return (
        <ul {...props}
            className={classes.join(' ')}>
            {props.flags.map(flag => (
                <li key={flag.name}>
                    <div className="pkt-flag-name">{flag.name}</div>
                    <div className="pkt-flag-description"><PktMarkdown>{flag.description}</PktMarkdown></div>
                </li>
            ))}
        </ul>
    );
}
