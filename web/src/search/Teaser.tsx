import React from 'react';
import {Card, Col, Row} from 'react-bootstrap';
import {Link} from 'react-router-dom';
import './Teaser.scss';

interface TeaserProps {
    label: string
    href: string
    image?: React.ReactElement
    /** The number of bootstrap columns the image should use */
    imageWidth?: number
    description?: React.ReactElement
}

export default function Teaser(props: TeaserProps) {
    const contents = (
        <>
            <Card.Title>
                <Link to={props.href}>
                    {props.label}
                </Link>
            </Card.Title>
            {props.description}
        </>
    );

    return (
        <Card className="pkt-teaser">
            <Card.Body>
                {props.image && (
                    <Row>
                        <Col sm={props.imageWidth ?? 1} className="pkt-teaser-image">
                            {props.image}
                        </Col>
                        <Col>
                            {contents}
                        </Col>
                    </Row>
                )}
                {!props.image && (
                    <>
                        {contents}
                    </>
                )}
            </Card.Body>
        </Card>
    );
}
