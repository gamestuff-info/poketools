import {ReactNode} from 'react';
import './EntityLabel.scss';

interface EntityLabelProps extends Record<string, any> {
    children: ReactNode
}

function EntityLabel(props: EntityLabelProps) {
    const classes = ['pkt-entitylabel'];
    if (props.className) {
        classes.push(...props.className.split(' '));
    }

    return (
        <div {...props} className={classes.join(' ')}>
            {props.children}
        </div>
    );
}

interface EntityLabelIconProps extends Record<string, any> {
    src: string
}

function EntityLabelIcon(props: EntityLabelIconProps) {
    const classes = ['pkt-entitylabel-icon', 'pkt-entityicon'];
    if (props.className) {
        classes.push(...props.className.split(' '));
    }
    return (<img alt="" {...props} className={classes.join(' ')} aria-hidden/>);
}

interface EntityLabelTextProps extends Record<string, any> {
    children: ReactNode
}

function EntityLabelText(props: EntityLabelTextProps) {
    const classes = ['pkt-entitylabel-text'];
    if (props.className) {
        classes.push(...props.className.split(' '));
    }
    return (<span {...props} className={classes.join(' ')}>{props.children}</span>);
}

EntityLabel.Icon = EntityLabelIcon;
EntityLabel.Text = EntityLabelText;
export default EntityLabel;
