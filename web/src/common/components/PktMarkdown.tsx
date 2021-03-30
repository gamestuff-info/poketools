import React from 'react';
import MathJax from 'mathjax3-react';

interface PktMarkdownPropsBase {
    children: string
}

type PktMarkdownProps = PktMarkdownPropsBase & Record<string, any>

export default function PktMarkdown(props: PktMarkdownProps) {
    const className = ['pkt-text'];
    if (props.className) {
        className.concat(props.className.split(' '));
    }

    return (
        <div className={className.join(' ')}>
            <MathJax.Html html={props.children}/>
        </div>
    );
}
